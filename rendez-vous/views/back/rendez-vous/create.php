<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Appointment – UniServe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; color: #333; }
        nav { background: #fff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,.08); }
        .logo { font-size: 22px; font-weight: bold; color: #1a237e; text-decoration: none; }
        .logo span { color: #26a69a; }
        .nav-links a { margin-left: 25px; text-decoration: none; color: #333; font-size: 14px; font-weight: 500; }

        .main { padding: 40px; max-width: 800px; margin: 0 auto; }
        .header h1 { font-size: 26px; color: #1a237e; margin-bottom: 25px; }
        .form-card { background: white; border-radius: 12px; padding: 35px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .form-group { margin-bottom: 18px; }
        label { font-size: 13px; font-weight: bold; color: #333; display: block; margin-bottom: 6px; }
        input, textarea { width: 100%; padding: 11px 14px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-submit { background: #1a237e; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; }
        .btn-cancel { color: #999; text-decoration: none; margin-left: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php?page=back" class="logo"><span>Uni</span>Serve</a>
        <div class="nav-links">
            <a href="index.php?page=back&module=appointments" class="active">Appointments</a>
            <a href="index.php?page=back&module=offices">Offices</a>
        </div>
    </nav>

    <div class="main">
        <div class="header"><h1>Add Appointment</h1></div>
        <div class="form-card">
            <form method="POST" action="index.php?page=back&action=store">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="titre" placeholder="Ex: Professor Meeting">
                </div>
                <button type="submit" class="btn-submit">Save Appointment</button>
                <a href="index.php?page=back" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html