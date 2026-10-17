# ============================================================
# Regole per la compilazione finale
# ============================================================
# Oggi l'offuscamento e' spento (isMinifyEnabled = false): l'app e' piccola e
# non c'e' niente da nascondere, anzi: chi la compila puo' leggerla tutta.
# Queste regole restano qui perche' se un giorno si accende l'offuscamento,
# il ponte fra la pagina e l'app deve restare intatto: i nomi dei metodi
# richiamati da JavaScript (window.LuxVerse.*) sono cercati per nome, e un
# nome cambiato dall'offuscamento spezza il ponte in silenzio.
# ============================================================

-keepclassmembers class org.luxverse.app.ui.MainActivity$WebBridge {
    public *;
}

-keep class org.luxverse.app.ui.MainActivity$WebBridge { *; }

# La WebView richiama i metodi annotati con @JavascriptInterface.
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}
