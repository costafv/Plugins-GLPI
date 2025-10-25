<?php
$config = [
    'layout'          => 'layout-b',
    'background_type' => 'image',
    'background'      => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1400&q=80',
    'welcome_message' => 'Bem-vindo ao ambiente de testes flvc.custom',
];

$backgroundStyle = strpos($config['background'], '#') === 0
    ? "background-color: {$config['background']};"
    : "background-image: url('{$config['background']}');";

$layoutClass = $config['layout'];
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Mock Login - flvc.custom</title>
</head>
<body>
<form name="login" method="post" action="#" class="mock-glpi-form">
    <h2>GLPI Login</h2>
    <label>
        Usuário
        <input type="text" name="login_name" placeholder="usuario@empresa.com">
    </label>
    <label>
        Senha
        <input type="password" name="login_password" placeholder="••••••••">
    </label>
    <button type="submit">Entrar</button>
</form>
<?php include __DIR__ . '/../flvc.custom/templates/login.php'; ?>
</body>
</html>
