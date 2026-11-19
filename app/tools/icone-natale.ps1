# ============================================================
# ICONE NATALIZIE DELL'APP (19/11/2026, 40.1.0)
# ------------------------------------------------------------
# Prende il marchio natalizio (Desktop\LUXNATALE.jpg) e ne ricava:
#   - le icone del telefono (mipmap-*), quadrate e tonde;
#   - lo strato in primo piano delle icone adattive (Android 8+);
#   - il logo grande che l'app mostra nella schermata di attesa.
#
# Si lancia da questa cartella:
#   powershell -NoProfile -ExecutionPolicy Bypass -File tools\icone-natale.ps1
# ============================================================

Add-Type -AssemblyName System.Drawing

$src   = Join-Path $env:USERPROFILE 'Desktop\LUXNATALE.jpg'
$res   = Join-Path $PSScriptRoot '..\app\src\main\res'
$sizes = @{ 'mdpi' = 48; 'hdpi' = 72; 'xhdpi' = 96; 'xxhdpi' = 144; 'xxxhdpi' = 192 }

if (-not (Test-Path $src)) { throw "Marchio non trovato: $src" }

$original = [System.Drawing.Image]::FromFile($src)

function New-Canvas([int]$w, [int]$h) {
    $bmp = New-Object System.Drawing.Bitmap($w, $h, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    return @($bmp, $g)
}

function Save-Round([System.Drawing.Image]$img, [int]$size, [string]$path) {
    $pair = New-Canvas $size $size
    $bmp = $pair[0]; $g = $pair[1]
    $g.Clear([System.Drawing.Color]::Black)
    $clip = New-Object System.Drawing.Drawing2D.GraphicsPath
    $clip.AddEllipse(0, 0, $size, $size)
    $g.SetClip($clip)
    $g.DrawImage($img, 0, 0, $size, $size)
    $g.ResetClip()
    $clip.Dispose(); $g.Dispose()
    $bmp.Save($path, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
}

function Save-Square([System.Drawing.Image]$img, [int]$size, [string]$path) {
    $pair = New-Canvas $size $size
    $bmp = $pair[0]; $g = $pair[1]
    $g.Clear([System.Drawing.Color]::Black)
    $g.DrawImage($img, 0, 0, $size, $size)
    $g.Dispose()
    $bmp.Save($path, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
}

function Save-Foreground([System.Drawing.Image]$img, [int]$size, [string]$path) {
    $pair = New-Canvas $size $size
    $bmp = $pair[0]; $g = $pair[1]
    $g.Clear([System.Drawing.Color]::Transparent)
    # Zona sicura: il marchio sta dentro il 64% centrale, cosi' nessuna
    # maschera del telefono (cerchio, goccia, quadrato) lo taglia.
    $inner = [int]($size * 0.64)
    $off   = [int](($size - $inner) / 2)
    $pair2 = New-Canvas $inner $inner
    $tmp = $pair2[0]; $gt = $pair2[1]
    $gt.Clear([System.Drawing.Color]::Black)
    $gt.DrawImage($img, 0, 0, $inner, $inner)
    $gt.Dispose()
    # Angoli arrotondati, cosi' lo strato non e' un quadrato nero dentro l'icona.
    $clip = New-Object System.Drawing.Drawing2D.GraphicsPath
    $r = [int]($inner * 0.22)
    $clip.AddArc(0, 0, $r, $r, 180, 90)
    $clip.AddArc($inner - $r, 0, $r, $r, 270, 90)
    $clip.AddArc($inner - $r, $inner - $r, $r, $r, 0, 90)
    $clip.AddArc(0, $inner - $r, $r, $r, 90, 90)
    $clip.CloseFigure()
    $g.SetClip($clip)
    $g.DrawImage($tmp, $off, $off, $inner, $inner)
    $g.ResetClip()
    $clip.Dispose(); $tmp.Dispose(); $g.Dispose()
    $bmp.Save($path, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
}

foreach ($k in $sizes.Keys) {
    $dir  = Join-Path $res "mipmap-$k"
    $dp   = $sizes[$k]
    Save-Square     $original $dp   (Join-Path $dir 'ic_launcher.png')
    Save-Round      $original $dp   (Join-Path $dir 'ic_launcher_round.png')
    Save-Foreground $original ($dp * 9 / 4) (Join-Path $dir 'ic_launcher_foreground.png')
    Write-Output "icone $k fatte ($dp px)"
}

# --- Logo grande per la schermata di attesa (fuori dagli mipmap: non e' un'icona) ---
$draw = Join-Path $res 'drawable-nodpi'
if (-not (Test-Path $draw)) { New-Item -ItemType Directory -Path $draw | Out-Null }
$logoPath = Join-Path $draw 'lux_natale.jpg'
$pair = New-Canvas 640 640
$bmp = $pair[0]; $g = $pair[1]
$g.Clear([System.Drawing.Color]::FromArgb(10, 11, 13))
$g.DrawImage($original, 0, 0, 640, 640)
$g.Dispose()
$codec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq 'image/jpeg' }
$params = New-Object System.Drawing.Imaging.EncoderParameters(1)
$params.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, 88L)
$bmp.Save($logoPath, $codec, $params)
$bmp.Dispose()
Write-Output "logo di attesa: $logoPath"

$original.Dispose()
