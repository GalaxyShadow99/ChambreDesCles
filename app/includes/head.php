<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chambre des Clés</title>
    <!-- Chargement de la police Inter -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            --bg-main: #f6f8fa;
            --border-color: #d0d7de;
            --text-main: #24292f;
            --text-muted: #57606a;
        }

        body {
            font-family: var(--font-sans);
            color: var(--text-main);
            background-color: var(--bg-main);
            font-size: 14px;
        }

        .navbar {
            border-bottom: 1px solid var(--border-color);
            padding: 10px 0;
        }

        /* Styles de tableau minimaliste style GitHub */
        .table-container {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background-color: #ffffff;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .table th {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-muted);
            background-color: #f6f8fa !important;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 10px 16px;
            user-select: none;
            cursor: pointer;
        }

        .table th:hover {
            background-color: #eaeef2 !important;
        }

        .table td {
            padding: 10px 16px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            font-size: 13px;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Alignement des en-têtes sorttable */
        #sorttable_sortfwdind, #sorttable_sortrevind {
            font-size: 10px;
            color: var(--text-muted);
            margin-left: 4px;
        }
    </style>
</head>
