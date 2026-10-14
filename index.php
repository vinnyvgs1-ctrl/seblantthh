<?php
/**
 * Redirección de seguridad e inmunidad a listado de directorios
 * Si un usuario ingresa a /civion/seblan_biometricos/ sin pasar por public/
 * o intenta ver el árbol de archivos, se redirige dinámicamente al Login del CRM.
 * 
 * Funciona de manera dinámica con cualquier dominio, subdominio o IP:
 * - https://civioncloud.ddns.net/civion/seg_Login/
 * - http://192.168.x.x/civion/seg_Login/
 * - https://subdominio.tudominio.com/civion/seg_Login/
 */

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Redirige al módulo de Login de Scriptcase respetando el host o IP con el que entró
header("Location: " . $protocol . $host . "/civion/seg_Login/", true, 302);
exit;
