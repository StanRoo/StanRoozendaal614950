# Define the log file path
$logFilePath = "C:\Users\Stan Roozendaal\StanRoozendaal614950\finalize.log"

# Start logging
Start-Transcript -Path $logFilePath -Append

# Log the start time of the script
Write-Output "Script started at: $(Get-Date)"

# Define the login credentials
$loginBody = @{
    username = "admin" 
    password = "Welkom#01!"
} | ConvertTo-Json

Write-Output "Attempting login with provided credentials..."

# Perform login to get the bearer token
$loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/login" `
    -Method POST `
    -Headers @{ "Content-Type" = "application/json" } `
    -Body $loginBody

# Check if login was successful and extract the token
if ($loginResponse -and $loginResponse.token) {
    $token = $loginResponse.token
    Write-Output "Login successful, token received."
} else {
    Write-Error "Login failed. Check credentials."
    Stop-Transcript  # Stop logging before exiting
    exit
}

# Log the success of the login
Write-Output "Token: $token"

# Define the headers with the Bearer token
$headers = @{
    "Authorization" = "Bearer $token"
}

# Call the 'finalize-expired' endpoint
Write-Output "Calling the 'finalize-expired' endpoint..."
$finalizeResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/marketplace/finalize-expired" `
    -Method POST `
    -Headers $headers

# Log the response from the finalize endpoint
Write-Output "Finalize Response: $finalizeResponse"

# Output the response from the finalize endpoint
Write-Output "Success processed results: $($finalizeResponse)"

# Log the end time of the script
Write-Output "Script ended at: $(Get-Date)"

# Stop logging
Stop-Transcript


