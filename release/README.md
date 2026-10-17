# Release

Questa cartella contiene l'APK pronto da allegare a una release di GitHub.

| File | Versione | Impronta SHA-256 |
|---|---|---|
| `LUXVerse-39.2.0.apk` | 39.2.0 (build 45) | `84c73d68686a5ad2f01c87e4d4a56b7f0536d71e20557578f52aeb1e439fee3b` |
| `LUXVerse-39.1.3.apk` | 39.1.3 (build 44, versione precedente) | `9bcecb4a8811ed5275aa5f36c01121e7a34dc2f2c24d7971726c7924637b79fd` |

L'APK **non va nel repository** (è in `.gitignore`): un file compilato non è codice e
appesantisce ogni futura modifica. Si allega invece alla *release*, dove GitHub lo mette a
disposizione come file scaricabile con il suo nome e la sua impronta.

## Come pubblicare una release

```bash
git tag -a v39.2.0 -m "L.U.X. Verse 39.2.0"
git push origin v39.2.0
```

Poi, dal sito: **Releases → Draft a new release**, scegli il tag appena creato, titolo
`L.U.X. Verse 39.2.0` e allega `LUXVerse-39.2.0.apk` trascinandolo nel riquadro degli
allegati.

Nel testo della release vale la pena scrivere cosa cambia, in due righe, e l'impronta
SHA-256 del file: così chi scarica può controllare che sia lo stesso file di cui si parla
nella pagina dell'app del sito.

Dopo la pubblicazione, carica lo stesso APK su VirusTotal e aggiorna il link nella pagina
dell'app (`site/app.php`, variabile `$vtUrl`) e nel `README.md`: un rapporto che non
corrisponde al file scaricabile confonde più di quanto aiuti.
