import os
import sys
import argparse
import pandas as pd
import mysql.connector
from datetime import datetime
from dotenv import load_dotenv

# Add project root to path for imports if needed
PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../'))
sys.path.append(PROJECT_ROOT)

def get_db_connection():
    load_dotenv(os.path.join(PROJECT_ROOT, '.env'))
    
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', 'localhost'),
        user=os.getenv('DB_USERNAME', 'root'),
        password=os.getenv('DB_PASSWORD', ''),
        database=os.getenv('DB_DATABASE', 'hamrolabs_db')
    )

def generate_pdf_report(df, title, subtitle, output_path):
    from reportlab.lib.pagesizes import letter, landscape
    from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
    from reportlab.lib.styles import getSampleStyleSheet
    from reportlab.lib import colors

    doc = SimpleDocTemplate(output_path, pagesize=landscape(letter))
    elements = []
    styles = getSampleStyleSheet()

    # Title
    elements.append(Paragraph(title, styles['Title']))
    elements.append(Spacer(1, 12))
    
    # Subtitle
    if subtitle:
        elements.append(Paragraph(subtitle, styles['Normal']))
        elements.append(Spacer(1, 12))

    # Table Data
    data = [df.columns.values.tolist()] + df.values.tolist()
    
    # Handle long strings to avoid overflow
    t = Table(data, repeatRows=1)
    
    # Add Style
    style = TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.grey),
        ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
        ('FONTSIZE', (0, 0), (-1, 0), 12),
        ('BOTTOMPADDING', (0, 0), (-1, 0), 12),
        ('BACKGROUND', (0, 1), (-1, -1), colors.beige),
        ('GRID', (0, 0), (-1, -1), 1, colors.black),
        ('FONTSIZE', (0, 1), (-1, -1), 10),
    ])
    t.setStyle(style)
    elements.append(t)
    
    doc.build(elements)

def generate_revenue_report(db, start_date, end_date, output_format, output_path):
    query = """
    SELECT 
        p.invoice_number,
        t.name as institute_name,
        p.amount,
        p.payment_method,
        p.status,
        p.paid_at
    FROM payments p
    JOIN tenants t ON p.tenant_id = t.id
    WHERE p.paid_at BETWEEN %s AND %s
    ORDER BY p.paid_at DESC
    """
    df = pd.read_sql(query, db, params=(start_date, end_date))
    
    if df.empty:
        df = pd.DataFrame(columns=['invoice_number', 'institute_name', 'amount', 'payment_method', 'status', 'paid_at'])

    if output_format == 'excel':
        with pd.ExcelWriter(output_path, engine='xlsxwriter') as writer:
            df.to_excel(writer, sheet_name='RevenueReport', index=False)
            workbook = writer.book
            worksheet = writer.sheets['RevenueReport']
            if not df.empty:
                header_format = workbook.add_format({'bold': True, 'bg_color': '#D7E4BC', 'border': 1})
                for col_num, value in enumerate(df.columns.values):
                    worksheet.write(0, col_num, value, header_format)
    elif output_format == 'pdf':
        generate_pdf_report(df, "Revenue Report", f"Period: {start_date} to {end_date}", output_path)

def generate_tenant_report(db, output_format, output_path):
    query = """
    SELECT name, subdomain, plan, status, sms_credits, student_limit, created_at
    FROM tenants
    ORDER BY created_at DESC
    """
    df = pd.read_sql(query, db)
    
    if output_format == 'excel':
        df.to_excel(output_path, index=False)
    elif output_format == 'pdf':
        generate_pdf_report(df, "Tenant Infrastructure Report", None, output_path)

def generate_users_report(db, output_format, output_path):
    query = """
    SELECT u.id, u.name, u.role, u.email, u.status, t.name as institute, u.created_at
    FROM users u
    LEFT JOIN tenants t ON u.tenant_id = t.id
    ORDER BY u.created_at DESC
    """
    df = pd.read_sql(query, db)
    
    if output_format == 'excel':
        df.to_excel(output_path, index=False)
    elif output_format == 'pdf':
        generate_pdf_report(df, "Users Account Report", None, output_path)

def generate_sms_report(db, start_date, end_date, output_format, output_path):
    query = """
    SELECT s.id, t.name as institute, s.recipient_no, s.message, s.gateway, s.status, s.created_at
    FROM sms_logs s
    LEFT JOIN tenants t ON s.tenant_id = t.id
    WHERE s.created_at BETWEEN %s AND %s
    ORDER BY s.created_at DESC
    """
    df = pd.read_sql(query, db, params=(start_date, end_date))
    
    if output_format == 'excel':
        df.to_excel(output_path, index=False)
    elif output_format == 'pdf':
        generate_pdf_report(df, "SMS Consumption Report", f"Period: {start_date} to {end_date}", output_path)

def generate_audit_report(db, start_date, end_date, output_format, output_path, type_filter=None):
    if type_filter == 'login':
        where_clause = "WHERE a.action = 'login' AND a.created_at BETWEEN %s AND %s"
        title = "Login History Report"
    else:
        where_clause = "WHERE a.created_at BETWEEN %s AND %s"
        title = "System Audit Report"
        
    query = f"""
    SELECT a.id, u.name as user, a.action, a.table_name, a.record_id, a.ip_address, a.created_at
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    {where_clause}
    ORDER BY a.created_at DESC
    """
    df = pd.read_sql(query, db, params=(start_date, end_date))
    
    if output_format == 'excel':
        df.to_excel(output_path, index=False)
    elif output_format == 'pdf':
        generate_pdf_report(df, title, f"Period: {start_date} to {end_date}", output_path)

def main():
    parser = argparse.ArgumentParser(description='Hamro ERP Super Admin Report Engine')
    parser.add_argument('--type', required=True, choices=['revenue', 'tenants', 'users', 'sms', 'audit', 'login'], help='Type of report')
    parser.add_argument('--format', required=True, choices=['excel', 'pdf'], help='Output format')
    parser.add_argument('--start', help='Start date (YYYY-MM-DD)')
    parser.add_argument('--end', help='End date (YYYY-MM-DD)')
    parser.add_argument('--output', required=True, help='Output file path')
    
    args = parser.parse_args()
    
    db = get_db_connection()
    if not args.start:
        args.start = '2000-01-01'
    if not args.end:
        args.end = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    else:
        args.end = f"{args.end} 23:59:59"

    try:
        if args.type == 'revenue':
            generate_revenue_report(db, args.start, args.end, args.format, args.output)
        elif args.type == 'tenants':
            generate_tenant_report(db, args.format, args.output)
        elif args.type == 'users':
            generate_users_report(db, args.format, args.output)
        elif args.type == 'sms':
            generate_sms_report(db, args.start, args.end, args.format, args.output)
        elif args.type == 'audit':
            generate_audit_report(db, args.start, args.end, args.format, args.output)
        elif args.type == 'login':
            generate_audit_report(db, args.start, args.end, args.format, args.output, type_filter='login')
        
        print(f"Report generated successfully: {args.output}")
    finally:
        db.close()

if __name__ == "__main__":
    main()
