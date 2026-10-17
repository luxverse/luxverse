# ============================================================
# LE ICONE DELL'APP — si rifanno dal disegno ufficiale
# ============================================================
# Le icone non si disegnano a mano e non si tengono come file misteriosi: si
# prendono i due file del marchio che stanno nel sito e si rimpiccioliscono
# nelle misure che Android chiede. Cosi', se il disegno cambia, si rilanciano
# le stesse istruzioni e le icone tornano uguali al nuovo disegno.
#
#   - site/assets/images/luxverse-official-mark.png  (il segno, con lo sfondo
#     trasparente)  -> icona adattiva di Android 8+ e disegno della schermata
#     di avvio;
#   - site/assets/images/luxverse-official.png       (il logo completo) ->
#     icona dei telefoni piu' vecchi (Android 7 e precedenti).
#
# Uso (da questa cartella):
#   powershell -ExecutionPolicy Bypass -File .\make-icons.ps1
#
# Le icone si scrivono in ..\app\src\main\res\mipmap-<misura>\.
# Serve Windows (usa il disegno di sistema .NET): non gira su Linux, dove
# invece gira la compilazione dell'app.
# ============================================================
param(
    [string]$Mark = '',
    [string]$Logo = '',
    [string]$Out  = '',
    [string]$Background = '#0A0B0D'
)

Add-Type -AssemblyName System.Drawing

$here = $PSScriptRoot
if (-not $Mark) { $Mark = (Resolve-Path (Join-Path $here '..\..\site\assets\images\luxverse-official-mark.png')).Path }
if (-not $Logo) { $Logo = (Resolve-Path (Join-Path $here '..\..\site\assets\images\luxverse-official.png')).Path }
if (-not $Out)  { $Out  = (Resolve-Path (Join-Path $here '..\app\src\main\res')).Path }

$bgColor = [System.Drawing.ColorTranslator]::FromHtml($Background)

# Le misure che Android chiede: nome della cartella, icona normale, icona
# adattiva (108dp, il 66% centrale e' la zona sicura: il disegno resta piu'
# piccolo e nessun telefono lo taglia).
$densities = @(
    @{ Dir = 'mdpi';    Icon = 48;  Adaptive = 108 },
    @{ Dir = 'hdpi';    Icon = 72;  Adaptive = 162 },
    @{ Dir = 'xhdpi';   Icon = 96;  Adaptive = 216 },
    @{ Dir = 'xxhdpi';  Icon = 144; Adaptive = 324 },
    @{ Dir = 'xxxhdpi'; Icon = 192; Adaptive = 432 }
)

function New-Canvas([int]$size) {
    $bmp = New-Object System.Drawing.Bitmap($size, $size, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.PixelOffsetMode   = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.SmoothingMode     = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    return @{ Bitmap = $bmp; Graphics = $g }
}

function Save-Png($bmp, [string]$path) {
    $dir = Split-Path $path -Parent
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    $bmp.Save($path, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
}

$logoImg = [System.Drawing.Image]::FromFile($Logo)
$markImg = [System.Drawing.Image]::FromFile($Mark)

foreach ($d in $densities) {
    $dir = Join-Path $Out ("mipmap-" + $d.Dir)
    $icon = $d.Icon
    $adapt = $d.Adaptive

    # --- 1. icona dei telefoni vecchi: il logo completo sullo sfondo scuro ---
    $c = New-Canvas $icon
    $c.Graphics.Clear($bgColor)
    $c.Graphics.DrawImage($logoImg, 0, 0, $icon, $icon)
    Save-Png $c.Bitmap (Join-Path $dir 'ic_launcher.png')

    # --- 2. la stessa, ritagliata a cerchio (telefoni che vogliono l'icona tonda) ---
    $c = New-Canvas $icon
    $c.Graphics.Clear([System.Drawing.Color]::Transparent)
    $circle = New-Object System.Drawing.Drawing2D.GraphicsPath
    $circle.AddEllipse(0, 0, $icon, $icon)
    $c.Graphics.SetClip($circle)
    $brush = New-Object System.Drawing.SolidBrush($bgColor)
    $c.Graphics.FillEllipse($brush, 0, 0, $icon, $icon)
    $c.Graphics.DrawImage($logoImg, 0, 0, $icon, $icon)
    $brush.Dispose(); $circle.Dispose()
    Save-Png $c.Bitmap (Join-Path $dir 'ic_launcher_round.png')

    # --- 3. icona adattiva: solo il segno, trasparente, dentro la zona sicura ---
    $c = New-Canvas $adapt
    $c.Graphics.Clear([System.Drawing.Color]::Transparent)
    $inside = [int][Math]::Round($adapt * 0.62)
    $off = [int][Math]::Round(($adapt - $inside) / 2)
    $c.Graphics.DrawImage($markImg, $off, $off, $inside, $inside)
    Save-Png $c.Bitmap (Join-Path $dir 'ic_launcher_foreground.png')

    Write-Output ("mipmap-{0,-8} icona {1,3}px  adattiva {2,3}px  segno {3,3}px" -f $d.Dir, $icon, $adapt, $inside)
}

$logoImg.Dispose()
$markImg.Dispose()

# Immagine per i negozi di applicazioni (512x512, come chiedono F-Droid e Play).
# Sta in app/fastlane/: e' la cartella che i negozi leggono per descrizione,
# immagini e note di versione.
$logoImg2 = [System.Drawing.Image]::FromFile($Logo)
$c = New-Canvas 512
$c.Graphics.Clear($bgColor)
$c.Graphics.DrawImage($logoImg2, 0, 0, 512, 512)
$logoImg2.Dispose()
$storeIcon = Join-Path $here '..\fastlane\metadata\android\en-US\images\icon.png'
Save-Png $c.Bitmap $storeIcon
Write-Output 'fastlane/metadata/android/en-US/images/icon.png  512px'
