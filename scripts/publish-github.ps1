# Pubblica il progetto su GitHub (repository nuova "luxury-sofa").
# Prerequisito: autenticazione una tantum — apri PowerShell ed esegui:
#   & "C:\Program Files\GitHub CLI\gh.exe" auth login
# Poi esegui questo script dalla cartella del progetto:
#   .\scripts\publish-github.ps1

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $ProjectRoot

$gh = "C:\Program Files\GitHub CLI\gh.exe"
if (-not (Test-Path $gh)) {
    Write-Error "GitHub CLI non trovato. Installa con: winget install GitHub.cli"
}

& $gh auth status 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Esegui prima: gh auth login" -ForegroundColor Yellow
    & $gh auth login
}

$repoName = "luxury-sofa"
$hasOrigin = & "C:\Program Files\Git\bin\git.exe" remote get-url origin 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "Remote 'origin' gia' presente. Push in corso..." -ForegroundColor Cyan
    & "C:\Program Files\Git\bin\git.exe" push -u origin main
    exit $LASTEXITCODE
}

& $gh repo create $repoName --public --source=. --remote=origin --description "Luxury Sofà - PHP storefront, admin, responsive UI" --push
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "Creazione automatica fallita. Crea il repo vuoto su github.com (nome: $repoName) poi:" -ForegroundColor Yellow
    Write-Host "  git remote add origin https://github.com/TUO_UTENTE/$repoName.git" -ForegroundColor White
    Write-Host "  git push -u origin main" -ForegroundColor White
}
