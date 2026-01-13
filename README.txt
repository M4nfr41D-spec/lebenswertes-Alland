# Lebenswertes Alland – GitHub Pages Paket

## Inhalt
- index.html
- assets/css/style.css
- assets/js/app.js
- assets/images/persoenliches-foto.svg (Platzhalter – ersetze mit deinem JPG/PNG)
- assets/pdf/unterstuetzungsformular.pdf (Platzhalter)

## Warum lokal manchmal nichts lädt?
Wenn du `index.html` per Doppelklick als `file:///...` öffnest, müssen die referenzierten Dateien (assets/...) **lokal** vorhanden sein.
WordPress-`wp-content` ist hier NICHT automatisch verfügbar – das geht nur, wenn du die Assets über **absolute https-URLs** einbindest.

## Deine echten Assets einsetzen
- Foto: ersetze `assets/images/persoenliches-foto.svg` durch dein `persoenliches-foto.jpg`
  und passe in index.html die src an.
- PDF: ersetze `assets/pdf/unterstuetzungsformular.pdf` durch dein echtes PDF.

## Mobile-Downscale Fix
Am Ende von style.css ist ein „fatal, aber sicher“ Override, der vw/zoom/scale-Probleme neutralisiert.
Wenn alles stabil ist, kann man die !important-Stellen später chirurgisch entfernen.
