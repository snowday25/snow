#!/usr/bin/env python3
"""
City Data Processor

Downloads and processes US cities data from SimpleMaps, filtering cities
with population > 100,000 and converting to JSON format.

Author: Snow Alerts Team
License: GPL v2 or later
"""

import os
import sys
import json
import zipfile
import tempfile
import shutil
from pathlib import Path

try:
    import pandas as pd
    import requests
except ImportError:
    print("Error: Required packages not installed.")
    print("Please run: pip install -r requirements.txt")
    sys.exit(1)


# Configuration
# NOTE: SimpleMaps URL contains a version number (1.92) which may need to be updated
# Check https://simplemaps.com/data/us-cities for the latest version
SIMPLEMAPS_URL = "https://simplemaps.com/static/data/us-cities/1.92/basic/simplemaps_uscities_basicv1.92.zip"
MIN_POPULATION = 100000
OUTPUT_FILE = "../data/us-cities-100k.json"

# Snow-prone states (states that regularly receive significant snowfall)
SNOW_PRONE_STATES = {
    'AK', 'CO', 'CT', 'ID', 'IL', 'IN', 'IA', 'ME', 'MA', 'MI', 
    'MN', 'MT', 'NH', 'NJ', 'NY', 'ND', 'OH', 'PA', 'RI', 'SD', 
    'UT', 'VT', 'WA', 'WV', 'WI', 'WY'
}


def download_file(url, dest_path):
    """
    Download a file from URL to destination path.
    
    Args:
        url: URL to download from
        dest_path: Path to save the file
        
    Returns:
        bool: True if successful, False otherwise
    """
    try:
        print(f"Downloading data from {url}...")
        response = requests.get(url, stream=True, timeout=30)
        response.raise_for_status()
        
        total_size = int(response.headers.get('content-length', 0))
        block_size = 8192
        downloaded = 0
        
        with open(dest_path, 'wb') as f:
            for chunk in response.iter_content(chunk_size=block_size):
                if chunk:
                    f.write(chunk)
                    downloaded += len(chunk)
                    if total_size > 0:
                        percent = (downloaded / total_size) * 100
                        print(f"\rProgress: {percent:.1f}%", end='')
        
        print("\nDownload complete!")
        return True
        
    except requests.RequestException as e:
        print(f"\nError downloading file: {e}")
        return False
    except Exception as e:
        print(f"\nUnexpected error during download: {e}")
        return False


def extract_zip(zip_path, extract_to):
    """
    Extract ZIP file to specified directory.
    
    Args:
        zip_path: Path to ZIP file
        extract_to: Directory to extract to
        
    Returns:
        str: Path to extracted CSV file, or None if failed
    """
    try:
        print(f"Extracting ZIP file...")
        with zipfile.ZipFile(zip_path, 'r') as zip_ref:
            zip_ref.extractall(extract_to)
        
        # Find the CSV file
        csv_files = list(Path(extract_to).glob('*.csv'))
        if csv_files:
            print(f"Found CSV file: {csv_files[0].name}")
            return str(csv_files[0])
        else:
            print("Error: No CSV file found in ZIP")
            return None
            
    except zipfile.BadZipFile:
        print("Error: Invalid ZIP file")
        return None
    except Exception as e:
        print(f"Error extracting ZIP: {e}")
        return None


def process_cities_data(csv_path):
    """
    Process cities CSV data and filter by population.
    
    Args:
        csv_path: Path to CSV file
        
    Returns:
        list: List of city dictionaries
    """
    try:
        print(f"Reading CSV data...")
        df = pd.read_csv(csv_path)
        
        print(f"Total cities in dataset: {len(df)}")
        
        # Filter cities with population > MIN_POPULATION
        df_filtered = df[df['population'] > MIN_POPULATION].copy()
        
        print(f"Cities with population > {MIN_POPULATION:,}: {len(df_filtered)}")
        
        # Sort by population (descending)
        df_filtered = df_filtered.sort_values('population', ascending=False)
        
        # Add ranking
        df_filtered['ranking'] = range(1, len(df_filtered) + 1)
        
        # Select and rename columns
        cities = []
        for _, row in df_filtered.iterrows():
            city_data = {
                'city': str(row.get('city', '')),
                'state': str(row.get('state_id', '')),
                'state_name': str(row.get('state_name', '')),
                'county': str(row.get('county_name', '')),
                'population': int(row.get('population', 0)),
                'latitude': float(row.get('lat', 0.0)),
                'longitude': float(row.get('lng', 0.0)),
                'timezone': str(row.get('timezone', '')),
                'ranking': int(row['ranking'])
            }
            cities.append(city_data)
        
        return cities
        
    except FileNotFoundError:
        print(f"Error: CSV file not found: {csv_path}")
        return None
    except pd.errors.EmptyDataError:
        print("Error: CSV file is empty")
        return None
    except Exception as e:
        print(f"Error processing CSV: {e}")
        return None


def save_json(cities, output_path):
    """
    Save cities data to JSON file.
    
    Args:
        cities: List of city dictionaries
        output_path: Path to save JSON file
        
    Returns:
        bool: True if successful, False otherwise
    """
    try:
        # Ensure output directory exists
        output_dir = Path(output_path).parent
        output_dir.mkdir(parents=True, exist_ok=True)
        
        print(f"Saving data to {output_path}...")
        with open(output_path, 'w', encoding='utf-8') as f:
            json.dump(cities, f, indent=2, ensure_ascii=False)
        
        print(f"Successfully saved {len(cities)} cities to JSON")
        return True
        
    except Exception as e:
        print(f"Error saving JSON: {e}")
        return False


def print_statistics(cities):
    """
    Print statistics about the processed cities data.
    
    Args:
        cities: List of city dictionaries
    """
    print("\n" + "="*60)
    print("STATISTICS")
    print("="*60)
    
    # Total cities
    print(f"\nTotal cities: {len(cities)}")
    
    # Population ranges
    if cities:
        populations = [city['population'] for city in cities]
        print(f"\nPopulation range:")
        print(f"  Largest: {max(populations):,} ({cities[0]['city']}, {cities[0]['state']})")
        print(f"  Smallest: {min(populations):,}")
        print(f"  Average: {sum(populations) // len(populations):,}")
    
    # Cities by state
    states = {}
    for city in cities:
        state = city['state']
        states[state] = states.get(state, 0) + 1
    
    print(f"\nCities by state (top 10):")
    sorted_states = sorted(states.items(), key=lambda x: x[1], reverse=True)
    for i, (state, count) in enumerate(sorted_states[:10], 1):
        print(f"  {i}. {state}: {count} cities")
    
    # Snow-prone states count
    snow_cities = [city for city in cities if city['state'] in SNOW_PRONE_STATES]
    print(f"\nSnow-prone state analysis:")
    print(f"  Cities in snow-prone states: {len(snow_cities)}")
    print(f"  Percentage: {len(snow_cities)/len(cities)*100:.1f}%")
    
    # Top 10 largest cities
    print(f"\nTop 10 largest cities:")
    for i, city in enumerate(cities[:10], 1):
        snow_marker = "❄️ " if city['state'] in SNOW_PRONE_STATES else ""
        print(f"  {i}. {snow_marker}{city['city']}, {city['state']} - {city['population']:,}")
    
    print("\n" + "="*60)


def main():
    """Main execution function."""
    print("="*60)
    print("US Cities Data Processor")
    print("="*60)
    print(f"Filtering cities with population > {MIN_POPULATION:,}\n")
    
    # Create temporary directory
    temp_dir = tempfile.mkdtemp()
    
    try:
        # Download ZIP file
        zip_path = os.path.join(temp_dir, 'cities.zip')
        if not download_file(SIMPLEMAPS_URL, zip_path):
            print("Failed to download data")
            return 1
        
        # Extract ZIP file
        csv_path = extract_zip(zip_path, temp_dir)
        if not csv_path:
            print("Failed to extract ZIP file")
            return 1
        
        # Process cities data
        cities = process_cities_data(csv_path)
        if not cities:
            print("Failed to process cities data")
            return 1
        
        # Save to JSON
        script_dir = Path(__file__).parent
        output_path = script_dir / OUTPUT_FILE
        if not save_json(cities, output_path):
            print("Failed to save JSON file")
            return 1
        
        # Print statistics
        print_statistics(cities)
        
        print("\n✓ Processing complete!")
        print(f"✓ Output file: {output_path}")
        
        return 0
        
    except KeyboardInterrupt:
        print("\n\nProcess interrupted by user")
        return 1
    except Exception as e:
        print(f"\nUnexpected error: {e}")
        import traceback
        traceback.print_exc()
        return 1
    finally:
        # Clean up temporary directory
        try:
            print("\nCleaning up temporary files...")
            shutil.rmtree(temp_dir)
            print("Cleanup complete")
        except Exception as e:
            print(f"Warning: Failed to clean up temporary files: {e}")


if __name__ == '__main__':
    sys.exit(main())
