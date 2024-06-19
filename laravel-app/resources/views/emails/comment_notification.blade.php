<!DOCTYPE html>
<html>
<head>
    <title>Уведомление</title>
</head>
<body>
<h1>Новый коммент</h1>
<p>Username: {{ $comment->user->username }}</p>
<p>Email: {{ $comment->user->email }}</p>
<p>Текст: {{ $comment->text }}</p>
</body>
</html>
