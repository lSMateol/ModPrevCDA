<style>
    @page {
        size: letter;
        margin: 0.5cm;
    }
    body {
        font-family: 'Arial', sans-serif;
        font-size: 7.5pt;
        line-height: 1.1;
        color: #000;
        margin: 0;
        padding: 0;
        background-color: #f4f7f9;
    }
    .container {
        width: 100%;
        max-width: 850px;
        margin: 0 auto;
        background-color: #fff;
        padding: 0.3cm;
        box-sizing: border-box;
        position: relative;
        z-index: 1;
    }
    header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 5px;
    }
    .header-left h1 {
        font-size: 20pt;
        margin: 0;
        font-weight: bold;
        color: #000;
    }
    .header-right {
        text-align: right;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    .business-info {
        font-size: 7.5pt;
        font-weight: bold;
        line-height: 1.2;
    }
    .logo {
        width: 65px;
        height: auto;
    }
    .order-info {
        text-align: left;
        margin-bottom: 2px;
        margin-top: 2px;
    }
    .order-info strong {
        font-size: 10pt;
    }
    .section-title {
        background-color: #f2f2f2;
        padding: 1px 5px;
        font-weight: bold;
        border: 1px solid #000;
        margin-top: 4px;
        font-size: 7.5pt;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
    }
    table, th, td {
        border: 1px solid #000;
    }
    th, td {
        padding: 1px 2px;
        text-align: left;
    }
    th {
        background-color: #f9f9f9;
        font-size: 8pt;
    }
    .label {
        font-weight: bold;
        background-color: #f2f2f2;
        width: 18%;
    }
    .value {
        width: 15.33%;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    
    .mechanized-section {
        font-size: 6.8pt;
    }
    .mechanized-section th {
        text-align: center;
        background: #eee;
    }
    
    .footer-signatures {
        margin-top: 10px;
        display: flex;
        justify-content: space-around;
    }
    .signature-box {
        width: 40%;
        border-top: 1px solid #000;
        text-align: center;
        padding-top: 5px;
        font-size: 8pt;
    }
    
    .photos-container {
        margin-top: 15px;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .photo-item {
        width: 170px;
        height: 120px;
        border: 1px solid #000;
        overflow: hidden;
        background: #f0f0f0;
    }
    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .status-box {
        border: 2px solid #000;
        padding: 5px 15px;
        display: inline-block;
        font-weight: bold;
        margin-top: 5px;
        font-size: 10pt;
    }

    .print-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #002D54;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 50px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        font-weight: bold;
        z-index: 1000;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .print-btn:hover {
        transform: scale(1.05);
        background-color: #003a6d;
    }

    @media print {
        .no-print { 
            display: none !important; 
        }
        body { 
            margin: 0 !important; 
            padding: 0 !important; 
            background-color: #fff !important; 
        }
        .container { 
            max-width: 100% !important; 
            margin: 0 !important; 
            padding: 0 !important; 
            box-shadow: none !important; 
            border-radius: 0 !important;
        }
        .watermark {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
