// ============================================================
// L.U.X. Verse — app Android (modulo :app)
// ============================================================
// Che app e': una finestra sul sito luxverse.c22.eu. Non e' una copia del
// sito: dentro c'e' solo la WebView a schermo intero, la schermata di
// attesa, la pagina di cortesia quando manca la rete, l'apertura dei
// collegamenti esterni nel browser e il salvataggio dei file.
//
// Cosa NON c'e', di proposito:
//   - nessuna chiave o password nell'APK (il pacchetto e' pubblico: chiunque
//     puo' aprirlo). Le chiavi delle API stanno solo sul server;
//   - nessuna libreria di tracciamento, nessuna pubblicita', nessuna
//     analytics: per questo l'app e' ammessa nei negozi che pretendono
//     software libero (F-Droid);
//   - nessun aggiornamento automatico: gli aggiornamenti li consegna il
//     negozio da cui l'app e' stata installata, non l'app da sola.
// ============================================================

plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

import java.io.FileInputStream
import java.util.Properties

// ------------------------------------------------------------
// CHIAVE DI FIRMA
// ------------------------------------------------------------
// La chiave privata con cui si firma l'APK pubblicato NON sta nel repository
// (vedi .gitignore): vive solo sulla macchina di chi pubblica, in
// keystore.properties. Senza quel file la compilazione riesce lo stesso e
// l'APK resta non firmato: e' cosi' che lo compila il servizio di F-Droid,
// che poi lo firma con la propria chiave.
val keystorePropsFile = rootProject.file("keystore.properties")
val hasKeystore = keystorePropsFile.exists()
val keystoreProps = Properties().apply {
    if (hasKeystore) FileInputStream(keystorePropsFile).use { load(it) }
}

android {
    namespace = "org.luxverse.app"
    compileSdk = 36

    defaultConfig {
        applicationId = "org.luxverse.app"
        minSdk = 24          // Android 7.0 e successivi
        targetSdk = 36
        versionCode = 52
        versionName = "39.5.0"

        // Indirizzi del sito: si cambiano qui e in nessun altro posto.
        //
        // L'app parte da webapp.php?app=1 e non dalla home: e' la pagina che
        // il sito stesso indica come casa quando si e' dentro l'app, e' meno
        // pesante della home (48 KB contro 80) e il sito la tiene aperta anche
        // durante la manutenzione, che invece coprirebbe la home. Il
        // parametro app=1 segna la visita come "dentro l'app": da li' in poi
        // tutti i rimandi del sito puntano alla pagina degli strumenti.
        buildConfigField("String", "SITE_URL", "\"https://luxverse.c22.eu\"")
        buildConfigField("String", "START_URL", "\"https://luxverse.c22.eu/webapp.php?app=1\"")
    }

    signingConfigs {
        create("release") {
            if (hasKeystore) {
                storeFile = rootProject.file(keystoreProps["storeFile"] as String)
                storePassword = keystoreProps["storePassword"] as String
                keyAlias = keystoreProps["keyAlias"] as String
                keyPassword = keystoreProps["keyPassword"] as String
                // Firme v2 e v3: bastano da Android 7 in poi, che e' la
                // versione minima di questa app (minSdk 24). La vecchia firma
                // v1 serviva fino ad Android 6 e non si applica piu': la v3,
                // in piu', e' quella che permette di cambiare chiave in
                // futuro senza perdere la continuita' degli aggiornamenti.
                enableV2Signing = true
                enableV3Signing = true
            }
        }
    }

    buildTypes {
        debug {
            applicationIdSuffix = ".debug"
            versionNameSuffix = "-debug"
        }
        release {
            isMinifyEnabled = false        // l'app e' piccola: nulla da offuscare
            isShrinkResources = false
            signingConfig = if (hasKeystore) signingConfigs.getByName("release") else null
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }

    buildFeatures {
        buildConfig = true
    }

    packaging {
        resources.excludes += setOf("META-INF/*.kotlin_module", "kotlin/**", "DebugProbesKt.bin")
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.16.0")
    implementation("androidx.appcompat:appcompat:1.7.0")
    implementation("androidx.webkit:webkit:1.12.1")
}
