<?php
if (function_exists('sqlsrv_connect')) {
    echo "✅ Función sqlsrv_connect existe y está disponible.";
} else {
    echo "❌ Función sqlsrv_connect NO existe.";
}
