@echo off
setlocal

REM Définir le token
set TOKEN=zfezezze

REM Vérifier la dernière version
curl -X POST http://localhost:3000/verify-token -H "Content-Type: application/json" -d "{\"token\": \"%TOKEN%\"}" > response.json

REM Extraire le lien de téléchargement
for /f "tokens=*" %%i in ('type response.json ^| jq -r ".data[0].downloadLink"') do set DOWNLOAD_LINK=%%i

REM Télécharger le fichier
curl -o mon_fichier.zip "%DOWNLOAD_LINK%"

REM Afficher un message de confirmation
echo Fichier téléchargé : mon_fichier.png

endlocal