# Rentify One-Click Deploy Script for Windows PowerShell
param (
    [string]$CommitMessage = "Deploy update $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
)

$ErrorActionPreference = "Stop"
$SshKey = "$HOME\Downloads\ssh-key-2026-10-24.key"
$VpsHost = "ubuntu@129.225.8.61"

Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ">>> MEMULAI DEPLOYMENT RENTIFY KE ORACLE VPS" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan

# 1. Cek perubahan lokal
$changes = git status --porcelain
if ($changes) {
    Write-Host "`n[1/3] Mendeteksi perubahan lokal. Melakukan Git commit dan push..." -ForegroundColor Yellow
    git add .
    git commit -m $CommitMessage
    git push origin main
    Write-Host "[OK] Git push berhasil!" -ForegroundColor Green
} else {
    Write-Host "`n[1/3] Tidak ada perubahan lokal baru. Melewati git commit." -ForegroundColor Cyan
}

# 2. Cek kunci SSH
if (-not (Test-Path $SshKey)) {
    Write-Host "`n[ERROR] Kunci SSH tidak ditemukan di $SshKey" -ForegroundColor Red
    exit 1
}

# 3. Jalankan deploy di Oracle VPS
Write-Host "`n[2/3] Menghubungkan ke Oracle VPS ($VpsHost) dan menjalankan deploy.sh..." -ForegroundColor Yellow
ssh -i $SshKey -o StrictHostKeyChecking=no $VpsHost "sudo bash /var/www/rentify/deploy.sh"

# 4. Status akhir
Write-Host "`n=============================================" -ForegroundColor Green
Write-Host ">>> DEPLOYMENT SELESAI DAN SUKSES!" -ForegroundColor Green
Write-Host "Aplikasi Live: https://rentify21.my.id" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green
