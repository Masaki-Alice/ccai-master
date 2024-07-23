import requests
import google.auth
from google.auth.transport.requests import Request

# Authenticate and get the access token
credentials, project_id = google.auth.default()
credentials.refresh(Request())
access_token = credentials.token

# Define the request URL
url = "https://contactcenterinsights.googleapis.com/v1/projects/ccai-dev-project/locations/us-central1/insightsdata:export"

# Define the headers
headers = {
    "Authorization": f"Bearer {access_token}",
    "Content-Type": "application/json; charset=utf-8"
}

# Define the JSON body for the request
data = {
    "bigQueryDestination": {
        "projectId": "ccai-dev-project",
        "dataset": "ccai_bq_looker",
        "table": "ccai_insights"
    },
    "filter": "latest_analysis:*",
    "writeDisposition": "WRITE_APPEND"
}

# Send the POST request
response = requests.post(url, headers=headers, json=data)

# Check the response
if response.status_code == 200:
    print("Request successful")
    print(response.json())
else:
    print("Request failed")
    print(response.status_code)
    print(response.text)
