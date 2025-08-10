#!/usr/bin/env python3
"""
IceSickleDuplicator System
Converts Excel files to CSV format when marker files are present
Part of the Snefuruplin WordPress Plugin
"""

import os
import sys
import json
import csv
from pathlib import Path
from datetime import datetime

try:
    import openpyxl
except ImportError:
    print("Error: openpyxl not installed. Install with: pip3 install openpyxl")
    sys.exit(1)

class IceSickleDuplicator:
    """Handles Excel to CSV conversion with marker file system"""
    
    MARKER_FILENAME = "IceSickleDuplicator.json"
    LOG_FILENAME = "IceSickleDuplicator.log"
    
    def __init__(self, base_path=None):
        """Initialize with optional base path"""
        self.base_path = Path(base_path) if base_path else Path.cwd()
        self.conversions = []
        
    def find_marker_files(self):
        """Find all IceSickleDuplicator marker files recursively"""
        markers = list(self.base_path.rglob(self.MARKER_FILENAME))
        print(f"Found {len(markers)} IceSickleDuplicator markers")
        return markers
    
    def convert_excel_to_csv(self, excel_path):
        """Convert Excel file to CSV format"""
        try:
            # Load Excel file
            wb = openpyxl.load_workbook(excel_path, data_only=True)
            
            # Get the first sheet (or active sheet)
            sheet = wb.active
            
            # Create CSV filename
            csv_path = excel_path.with_suffix('.csv')
            
            # Write to CSV
            with open(csv_path, 'w', newline='', encoding='utf-8') as csvfile:
                csv_writer = csv.writer(csvfile)
                
                # Write all rows
                for row in sheet.iter_rows(values_only=True):
                    csv_writer.writerow(row)
            
            return csv_path, None
            
        except Exception as e:
            return None, str(e)
    
    def process_marker(self, marker_path):
        """Process a single marker file and its associated Excel files"""
        directory = marker_path.parent
        
        # Read marker configuration
        config = self.read_marker_config(marker_path)
        
        # Find Excel files in the same directory
        excel_files = list(directory.glob('*.xlsx')) + list(directory.glob('*.xls'))
        
        # Filter out temporary files (starting with ~$)
        excel_files = [f for f in excel_files if not f.name.startswith('~$')]
        
        results = []
        for excel_file in excel_files:
            csv_path, error = self.convert_excel_to_csv(excel_file)
            
            result = {
                'excel_file': str(excel_file),
                'csv_file': str(csv_path) if csv_path else None,
                'success': csv_path is not None,
                'error': error,
                'timestamp': datetime.now().isoformat()
            }
            results.append(result)
            
            if csv_path:
                print(f"✓ Converted: {excel_file.name} → {csv_path.name}")
            else:
                print(f"✗ Failed: {excel_file.name} - {error}")
        
        # Update log file
        self.update_log(directory, results)
        
        return results
    
    def read_marker_config(self, marker_path):
        """Read configuration from marker file"""
        try:
            with open(marker_path, 'r') as f:
                return json.load(f)
        except:
            # Return default config if file is empty or invalid
            return {
                'created': datetime.now().isoformat(),
                'auto_convert': True,
                'keep_original': True
            }
    
    def update_log(self, directory, results):
        """Update the log file with conversion results"""
        log_path = directory / self.LOG_FILENAME
        
        log_data = {
            'last_run': datetime.now().isoformat(),
            'conversions': results
        }
        
        # Append to existing log if it exists
        if log_path.exists():
            try:
                with open(log_path, 'r') as f:
                    existing_log = json.load(f)
                    if 'history' not in existing_log:
                        existing_log['history'] = []
                    existing_log['history'].append(log_data)
                    log_data = existing_log
            except:
                pass
        
        with open(log_path, 'w') as f:
            json.dump(log_data, f, indent=2)
    
    def run_all(self):
        """Run conversion for all marker files found"""
        markers = self.find_marker_files()
        
        if not markers:
            print("No IceSickleDuplicator markers found")
            return
        
        all_results = {}
        for marker in markers:
            print(f"\nProcessing: {marker.parent}")
            results = self.process_marker(marker)
            all_results[str(marker.parent)] = results
        
        return all_results


def main():
    """Main entry point for command line usage"""
    import argparse
    
    parser = argparse.ArgumentParser(description='IceSickleDuplicator - Excel to CSV Converter')
    parser.add_argument('--path', type=str, default='.', 
                       help='Base path to search for markers (default: current directory)')
    parser.add_argument('--single', type=str, 
                       help='Convert a single Excel file directly')
    
    args = parser.parse_args()
    
    if args.single:
        # Single file conversion
        duplicator = IceSickleDuplicator()
        excel_path = Path(args.single)
        if excel_path.exists():
            csv_path, error = duplicator.convert_excel_to_csv(excel_path)
            if csv_path:
                print(f"Success: Created {csv_path}")
            else:
                print(f"Error: {error}")
        else:
            print(f"Error: File not found: {excel_path}")
    else:
        # Marker-based conversion
        duplicator = IceSickleDuplicator(args.path)
        duplicator.run_all()


if __name__ == "__main__":
    main()