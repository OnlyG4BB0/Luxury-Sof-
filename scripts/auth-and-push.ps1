# Autentica GitHub CLI e pubblica su https://github.com/<utente>/luxury-sofa
# Uso:
#   1) (consigliato) Crea un file .github-token nella root del progetto con UN SOLO rigo: il tuo PAT (repo scope).
#   2) Oppure imposta: $env:GH_TOKEN = "ghp_..."
#   3) Esegui: .\scripts\auth-and-push.ps1

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

$gh = "C:\Program Files\GitHub CLI\gh.exe"
$git = "C:\Program Files\Git\bin\git.exe"

function Get-Token {
    if ($env:GH_TOKEN) { return $env:GH_TOKEN.Trim() }
    $p = Join-Path $Root ".github-token"
    if (Test-Path $p) {
        return (Get-Content $p -Raw).Trim()
    }
    return $null
}

$token = Get-Token
if ($token) {
    $token | & $gh auth login --with-token 2>&1
    if ($LASTEXITCODE -ne 0) { throw "Autenticazione con token fallita." }
} else {
    Write-Host "Nessun token: avvio accesso da browser (GitHub Device Flow)..." -ForegroundColor Cyan
    & $gh auth login -h github.com -p https -w
    if ($LASTEXITCODE -ne 0) { throw "gh auth login non completato." }
}

& $gh auth status
$remote = & $git remote get-url origin 2>$null
if ($LASTEXITCODE -ne 0) {
    & $gh repo create luxury-sofa --public --source=. --remote=origin --description "Luxury Sofà - PHP storefront, admin, responsive UI" --push
} else {
    & $git push -u origin main
}

Write-Host "Fatto." -ForegroundColor Green
