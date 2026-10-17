// ============================================================
// L.U.X. Verse — impostazioni del progetto
// ============================================================
// Un solo modulo (:app). I repository da cui si prendono le librerie sono
// solo quelli pubblici e aperti: Google (androidx) e Maven Central. Niente
// Firebase, niente Play Services, niente librerie di tracciamento: l'app non
// ha pubblicità e non manda da nessuna parte quello che fai.
// ============================================================

pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}

rootProject.name = "LuxVerse"
include(":app")
