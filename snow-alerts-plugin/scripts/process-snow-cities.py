#!/usr/bin/env python3
"""
Process Snow Cities Script

This script generates a comprehensive JSON file of all snow-prone US locations
without population filtering, including cities, towns, villages, and localities
from 39 snow-prone states.

Usage:
    python process-snow-cities.py [--output OUTPUT_FILE]

Requirements:
    See requirements.txt
"""

import json
import argparse
import sys

def get_snow_states():
    """Get list of 39 snow-prone US states"""
    return [
        'AK', 'CO', 'CT', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY',
        'ME', 'MD', 'MA', 'MI', 'MN', 'MO', 'MT', 'NE', 'NV',
        'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR',
        'PA', 'RI', 'SD', 'TN', 'UT', 'VT', 'VA', 'WA', 'WV',
        'WI', 'WY'
    ]

def generate_sample_locations():
    """
    Generate sample locations for demonstration.
    
    In production, this would connect to a geographic database API
    like GeoNames, US Census Bureau, or similar service to fetch
    comprehensive location data.
    """
    
    # Sample locations - expanded dataset
    locations = [
        # Alaska
        {"name": "Anchorage", "state": "AK", "type": "city", "lat": 61.2181, "lon": -149.9003, "population": 291538, "elevation": 102},
        {"name": "Fairbanks", "state": "AK", "type": "city", "lat": 64.8378, "lon": -147.7164, "population": 32193, "elevation": 436},
        {"name": "Juneau", "state": "AK", "type": "city", "lat": 58.3019, "lon": -134.4197, "population": 32255, "elevation": 0},
        
        # Colorado
        {"name": "Denver", "state": "CO", "type": "city", "lat": 39.7392, "lon": -104.9903, "population": 715522, "elevation": 5280},
        {"name": "Boulder", "state": "CO", "type": "city", "lat": 40.0150, "lon": -105.2705, "population": 108090, "elevation": 5328},
        {"name": "Aspen", "state": "CO", "type": "city", "lat": 39.1911, "lon": -106.8175, "population": 7004, "elevation": 7908},
        {"name": "Vail", "state": "CO", "type": "town", "lat": 39.6403, "lon": -106.3742, "population": 5305, "elevation": 8150},
        
        # New York
        {"name": "Buffalo", "state": "NY", "type": "city", "lat": 42.8864, "lon": -78.8784, "population": 258612, "elevation": 600},
        {"name": "Syracuse", "state": "NY", "type": "city", "lat": 43.0481, "lon": -76.1474, "population": 142553, "elevation": 380},
        {"name": "Rochester", "state": "NY", "type": "city", "lat": 43.1566, "lon": -77.6088, "population": 206284, "elevation": 505},
        {"name": "Albany", "state": "NY", "type": "city", "lat": 42.6526, "lon": -73.7562, "population": 99224, "elevation": 275},
        
        # Minnesota
        {"name": "Minneapolis", "state": "MN", "type": "city", "lat": 44.9778, "lon": -93.2650, "population": 425115, "elevation": 830},
        {"name": "St. Paul", "state": "MN", "type": "city", "lat": 44.9537, "lon": -93.0900, "population": 307193, "elevation": 795},
        {"name": "Duluth", "state": "MN", "type": "city", "lat": 46.7867, "lon": -92.1005, "population": 86697, "elevation": 602},
        
        # Wisconsin
        {"name": "Milwaukee", "state": "WI", "type": "city", "lat": 43.0389, "lon": -87.9065, "population": 590157, "elevation": 617},
        {"name": "Madison", "state": "WI", "type": "city", "lat": 43.0731, "lon": -89.4012, "population": 269840, "elevation": 873},
        {"name": "Green Bay", "state": "WI", "type": "city", "lat": 44.5133, "lon": -88.0133, "population": 105207, "elevation": 581},
        
        # Illinois
        {"name": "Chicago", "state": "IL", "type": "city", "lat": 41.8781, "lon": -87.6298, "population": 2746388, "elevation": 597},
        {"name": "Rockford", "state": "IL", "type": "city", "lat": 42.2711, "lon": -89.0940, "population": 147051, "elevation": 718},
        
        # Michigan
        {"name": "Detroit", "state": "MI", "type": "city", "lat": 42.3314, "lon": -83.0458, "population": 639111, "elevation": 600},
        {"name": "Grand Rapids", "state": "MI", "type": "city", "lat": 42.9634, "lon": -85.6681, "population": 198917, "elevation": 610},
        {"name": "Marquette", "state": "MI", "type": "city", "lat": 46.5436, "lon": -87.3954, "population": 20629, "elevation": 620},
        
        # Ohio
        {"name": "Cleveland", "state": "OH", "type": "city", "lat": 41.4993, "lon": -81.6944, "population": 372624, "elevation": 653},
        {"name": "Columbus", "state": "OH", "type": "city", "lat": 39.9612, "lon": -82.9988, "population": 905748, "elevation": 902},
        
        # Pennsylvania
        {"name": "Pittsburgh", "state": "PA", "type": "city", "lat": 40.4406, "lon": -79.9959, "population": 302205, "elevation": 1223},
        {"name": "Philadelphia", "state": "PA", "type": "city", "lat": 39.9526, "lon": -75.1652, "population": 1584064, "elevation": 39},
        {"name": "Erie", "state": "PA", "type": "city", "lat": 42.1292, "lon": -80.0851, "population": 95508, "elevation": 732},
        
        # Massachusetts
        {"name": "Boston", "state": "MA", "type": "city", "lat": 42.3601, "lon": -71.0589, "population": 692600, "elevation": 141},
        {"name": "Worcester", "state": "MA", "type": "city", "lat": 42.2626, "lon": -71.8023, "population": 185428, "elevation": 480},
        
        # Maine
        {"name": "Portland", "state": "ME", "type": "city", "lat": 43.6591, "lon": -70.2568, "population": 66215, "elevation": 61},
        {"name": "Bangor", "state": "ME", "type": "city", "lat": 44.8016, "lon": -68.7712, "population": 31753, "elevation": 159},
        
        # Vermont
        {"name": "Burlington", "state": "VT", "type": "city", "lat": 44.4759, "lon": -73.2121, "population": 42545, "elevation": 200},
        {"name": "Montpelier", "state": "VT", "type": "city", "lat": 44.2601, "lon": -72.5754, "population": 7855, "elevation": 525},
        
        # New Hampshire
        {"name": "Manchester", "state": "NH", "type": "city", "lat": 42.9956, "lon": -71.4548, "population": 115644, "elevation": 175},
        {"name": "Concord", "state": "NH", "type": "city", "lat": 43.2081, "lon": -71.5376, "population": 43627, "elevation": 288},
        
        # Utah
        {"name": "Salt Lake City", "state": "UT", "type": "city", "lat": 40.7608, "lon": -111.8910, "population": 200567, "elevation": 4226},
        {"name": "Park City", "state": "UT", "type": "city", "lat": 40.6461, "lon": -111.4980, "population": 8396, "elevation": 7000},
        
        # Idaho
        {"name": "Boise", "state": "ID", "type": "city", "lat": 43.6150, "lon": -116.2023, "population": 228959, "elevation": 2730},
        {"name": "Coeur d'Alene", "state": "ID", "type": "city", "lat": 47.6777, "lon": -116.7805, "population": 51491, "elevation": 2188},
        
        # Washington
        {"name": "Seattle", "state": "WA", "type": "city", "lat": 47.6062, "lon": -122.3321, "population": 753675, "elevation": 175},
        {"name": "Spokane", "state": "WA", "type": "city", "lat": 47.6588, "lon": -117.4260, "population": 222081, "elevation": 1843},
        
        # North Dakota
        {"name": "Fargo", "state": "ND", "type": "city", "lat": 46.8772, "lon": -96.7898, "population": 125990, "elevation": 902},
        {"name": "Bismarck", "state": "ND", "type": "city", "lat": 46.8083, "lon": -100.7837, "population": 73622, "elevation": 1686},
        
        # South Dakota
        {"name": "Sioux Falls", "state": "SD", "type": "city", "lat": 43.5446, "lon": -96.7311, "population": 192517, "elevation": 1470},
        {"name": "Rapid City", "state": "SD", "type": "city", "lat": 44.0805, "lon": -103.2310, "population": 77503, "elevation": 3202},
    ]
    
    return locations

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='Process snow-prone US locations')
    parser.add_argument('--output', '-o', default='../data/us-snow-locations.json',
                      help='Output JSON file path')
    
    args = parser.parse_args()
    
    print("Generating snow location data...")
    print(f"Target states: {len(get_snow_states())} snow-prone states")
    
    locations = generate_sample_locations()
    
    print(f"Generated {len(locations)} sample locations")
    print(f"Writing to {args.output}...")
    
    try:
        with open(args.output, 'w', encoding='utf-8') as f:
            json.dump(locations, f, indent=2, ensure_ascii=False)
        
        print("Success! Location data generated.")
        print(f"\nNote: This is a sample dataset with {len(locations)} locations.")
        print("For production use, integrate with a comprehensive geographic database")
        print("such as GeoNames, US Census Bureau, or similar API to generate")
        print("the full 7,000-15,000 location dataset.")
        
    except Exception as e:
        print(f"Error writing file: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == '__main__':
    main()
