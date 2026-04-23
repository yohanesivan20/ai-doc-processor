<!-- resources/views/layout.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>AI Invoices Processor</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">AI Invoices Processor</span>
        </div>
    </nav>

    <div class="container mt-4 mb-2">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>
    @yield('scripts')
</html>