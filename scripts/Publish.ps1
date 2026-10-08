param([string]$Message = 'Publish XuVerse content', [switch]$Prepare)
$ErrorActionPreference = 'Stop'
$publishPython = if ($env:XUVERSE_PYTHON) { $env:XUVERSE_PYTHON } else { 'python' }
$publishArgs = @("$PSScriptRoot/publish.py", '--message', $Message)
if ($Prepare) { $publishArgs += '--prepare' }
& $publishPython @publishArgs
if ($LASTEXITCODE -ne 0) { throw 'Publish did not complete. Read the preceding error.' }
