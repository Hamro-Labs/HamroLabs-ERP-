import re

def extract_table(filename, table_name):
    with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        pattern = re.compile(rf'CREATE TABLE `{table_name}`.*?;', re.DOTALL)
        match = pattern.search(content)
        if match:
            print(match.group(0))
        else:
            print(f"Table {table_name} not found.")

filename = r'c:\Apache24\htdocs\erp\database\realdb.sql'
extract_table(filename, 'payment_transactions')
extract_table(filename, 'fee_records')
extract_table(filename, 'fee_items')
