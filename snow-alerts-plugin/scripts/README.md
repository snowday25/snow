# Snow Location Processing Scripts

This directory contains scripts for generating and managing the snow location database.

## Scripts

### process-snow-cities.py

Generates a comprehensive JSON file of snow-prone US locations.

**Usage:**
```bash
python process-snow-cities.py [--output OUTPUT_FILE]
```

**Options:**
- `--output`, `-o`: Output JSON file path (default: `../data/us-snow-locations.json`)

**Example:**
```bash
# Generate with default output
python process-snow-cities.py

# Generate with custom output
python process-snow-cities.py --output /path/to/custom/locations.json
```

## Requirements

The basic script uses only Python 3 standard library. No additional packages required.

For advanced features (future development):
```bash
pip install -r requirements.txt
```

## Location Data Structure

Each location in the JSON file has the following structure:

```json
{
  "name": "Denver",
  "state": "CO",
  "type": "city",
  "lat": 39.7392,
  "lon": -104.9903,
  "population": 715522,
  "elevation": 5280
}
```

**Fields:**
- `name` (string): Location name
- `state` (string): Two-letter state code
- `type` (string): Location type (city, town, village, locality)
- `lat` (float): Latitude
- `lon` (float): Longitude  
- `population` (int): Population count
- `elevation` (int, optional): Elevation in feet

## Snow-Prone States

The script targets 39 US states with significant snowfall:

**High Snow States:**
- Alaska (AK)
- Colorado (CO)
- Idaho (ID)
- Maine (ME)
- Minnesota (MN)
- Montana (MT)
- New Hampshire (NH)
- New York (NY)
- North Dakota (ND)
- Utah (UT)
- Vermont (VT)
- Wisconsin (WI)
- Wyoming (WY)

**Moderate Snow States:**
- Connecticut (CT)
- Illinois (IL)
- Indiana (IN)
- Iowa (IA)
- Kansas (KS)
- Massachusetts (MA)
- Michigan (MI)
- Nebraska (NE)
- Nevada (NV)
- New Jersey (NJ)
- Ohio (OH)
- Oregon (OR)
- Pennsylvania (PA)
- Rhode Island (RI)
- South Dakota (SD)
- Washington (WA)
- West Virginia (WV)

**Occasional Snow States:**
- Kentucky (KY)
- Maryland (MD)
- Missouri (MO)
- New Mexico (NM)
- North Carolina (NC)
- Oklahoma (OK)
- Tennessee (TN)
- Virginia (VA)

## Production Data Sources

For production use with 7,000-15,000+ locations, consider integrating with:

1. **GeoNames** (https://www.geonames.org/)
   - Free geographic database
   - Comprehensive US location data
   - Requires free account

2. **US Census Bureau API** (https://www.census.gov/data/developers.html)
   - Official government data
   - Free API access
   - Detailed location information

3. **SimpleMaps** (https://simplemaps.com/data/us-cities)
   - Commercial database
   - High quality data
   - Paid license required

## Notes

- The sample script generates ~50 major cities as demonstration
- For full production dataset, integrate with external geographic database
- Location data should be refreshed annually
- Ensure compliance with data source terms of service
