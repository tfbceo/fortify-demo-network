"""
Build plugins/fortify-offices/data/office-zips.json from the Census Bureau's
2020 ZCTA-to-county relationship file.

    python -I scripts/tools/build-zip-map.py <path>/tab20_zcta520_county20_natl.txt

Source: https://www2.census.gov/geo/docs/maps-data/data/rel2020/zcta520/tab20_zcta520_county20_natl.txt
(the file is not kept in this repo; 6.8 MB, last modified 2021-12-09).

Rules (approved 2026-11-22):
- A ZCTA belongs to the county holding most of its land area.
- Whole-county offices take every ZCTA of their counties.
- Palm Beach County is split by ZIP: Boynton Beach and south go to the
  Boca Raton-Delray Beach office, the rest to West Palm Beach-Jupiter.
- Exception: 33469 (Tequesta) goes to West Palm Beach-Jupiter, although most
  of its land area is in Martin County.
ZCTAs approximate ZIP codes; PO-box-only ZIPs are not in the Census data.
"""
import csv
import json
import os
import sys
from collections import defaultdict

COUNTY_OFFICE = {
    '12057': 'tampa',            # Hillsborough
    '12103': 'st-petersburg',    # Pinellas
    '12095': 'orlando',          # Orange
    '12117': 'orlando',          # Seminole
    '12115': 'sarasota',         # Sarasota
    '12081': 'sarasota',         # Manatee
    '12105': 'lakeland',         # Polk
    '12086': 'miami',            # Miami-Dade
    '12011': 'fort-lauderdale',  # Broward
    '12021': 'naples',           # Collier
    '04013': 'phoenix',          # Maricopa
    '47037': 'nashville',        # Davidson
    '47187': 'nashville',        # Williamson
    '12099': None,               # Palm Beach: split below
}
# Palm Beach: Boynton Beach, Delray Beach and Boca Raton postal areas.
PALM_BEACH_SOUTH = {
    '33426', '33435', '33436', '33437', '33472', '33473',           # Boynton Beach
    '33444', '33445', '33446', '33483', '33484',                    # Delray Beach
    '33428', '33431', '33432', '33433', '33434', '33486', '33487',  # Boca Raton
    '33496', '33498',
}
EXTRA = {'33469': 'west-palm-beach'}  # Tequesta


def main(path):
    parts = defaultdict(list)
    with open(path, encoding='utf-8-sig') as fh:
        for row in csv.DictReader(fh, delimiter='|'):
            z = row['GEOID_ZCTA5_20']
            if z:
                parts[z].append((int(row['AREALAND_PART'] or 0), row['GEOID_COUNTY_20']))
    offices = defaultdict(set)
    for z, ps in parts.items():
        county = max(ps)[1]
        if county not in COUNTY_OFFICE:
            continue
        office = COUNTY_OFFICE[county]
        if county == '12099':
            office = 'boca-raton' if z in PALM_BEACH_SOUTH else 'west-palm-beach'
        offices[office].add(z)
    for z, office in EXTRA.items():
        offices[office].add(z)
    out = {
        '_source': 'US Census Bureau, 2020 ZCTA to County Relationship File (tab20_zcta520_county20_natl.txt)',
        '_rules': 'Majority land area; Palm Beach split by ZIP (Boynton Beach and south to boca-raton); 33469 added to west-palm-beach.',
        'offices': {k: sorted(v) for k, v in sorted(offices.items())},
    }
    dest = os.path.join(os.path.dirname(__file__), '..', '..', 'plugins', 'fortify-offices', 'data', 'office-zips.json')
    with open(dest, 'w', encoding='utf-8', newline='\n') as fh:
        json.dump(out, fh, indent=1)
        fh.write('\n')
    for k, v in out['offices'].items():
        print(f'{k:16} {len(v)}')
    print('total', sum(len(v) for v in out['offices'].values()))


if __name__ == '__main__':
    main(sys.argv[1])
