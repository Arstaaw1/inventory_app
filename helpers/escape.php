<?php
// Mencegah XSS Attack saat menampilkan data ke HTML
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}