$data = Get-Content 'd:\Download\pi-session-2026-06-10T02-46-54-981Z_019eaf6c-f905-7dc5-b76b-7263164672c7.html' -Raw
$jsonStart = $data.IndexOf('<script id="session-data"')
$jsonEnd = $data.IndexOf('</script>', $jsonStart)
$encoded = $data.Substring($jsonStart + 35, $jsonEnd - $jsonStart - 35)
$decoded = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($encoded))
$decoded | ConvertFrom-Json | ConvertTo-Json -Depth 10
