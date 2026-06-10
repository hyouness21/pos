@echo off
title POS System
color 0A
echo Starting POS System...
start http://localhost:8000
php artisan serve
