plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

import java.util.Properties
import java.io.FileInputStream

val keystoreProperties = Properties()
val keystorePropsFile = rootProject.file("keystore.properties")
if (keystorePropsFile.exists()) {
    keystoreProperties.load(FileInputStream(keystorePropsFile))
}
val hasKeystore = keystorePropsFile.exists()

android {
    namespace = "com.workmeai.app"
    compileSdk = 36

    defaultConfig {
        applicationId = "com.workmeai.app"
        minSdk = 24
        targetSdk = 36
versionCode = 44
versionName = "39.1.3"

        // Niente chiavi dentro l'APK: il pacchetto è pubblico e chiunque può aprirlo.
        // Le chiavi AI e il token vivono solo sul server (data/vault.local.php).
        buildConfigField("String", "GROQ_API_KEY", "\"\"")
        buildConfigField("String", "GROQ_MODEL", "\"openai/gpt-oss-120b\"")
        buildConfigField("String", "GROQ_URL", "\"https://api.groq.com/openai/v1/chat/completions\"")
        buildConfigField("String", "API_TOKEN", "\"\"")
        buildConfigField("String", "SITE_URL", "\"https://luxverse.c22.eu\"")
        buildConfigField("String", "APP_URL", "\"https://luxverse.c22.eu/webapp.php?app=1\"")
    }

    signingConfigs {
        create("release") {
            if (hasKeystore) {
                storeFile = rootProject.file(keystoreProperties["storeFile"] as String)
                storePassword = keystoreProperties["storePassword"] as String
                keyAlias = keystoreProperties["keyAlias"] as String
                keyPassword = keystoreProperties["keyPassword"] as String
            }
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = false
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
        viewBinding = true
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.16.0")
    implementation("androidx.appcompat:appcompat:1.7.0")
    implementation("com.google.android.material:material:1.12.0")
    implementation("androidx.constraintlayout:constraintlayout:2.2.1")
    implementation("androidx.recyclerview:recyclerview:1.3.2")
    implementation("androidx.webkit:webkit:1.12.1")
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.9.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
}