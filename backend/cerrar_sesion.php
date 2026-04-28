<?php
// 1. Reanudamos la sesión existente
session_start();

// 2. Vaciamos todas las variables de sesión ($_SESSION)
session_unset();

// 3. Destruimos la sesión por completo
session_destroy();

// 4. Redirigimos al Login (asumiendo que tu login se llama index.php)
// Si tu login se llama diferente, cámbialo aquí abajo.
header("Location: ../index.html");
exit();
?>