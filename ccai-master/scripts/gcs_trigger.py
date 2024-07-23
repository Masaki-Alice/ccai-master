import functions_framework
import requests

# Triggered by a when a file is uploaded in a storage bucket
@functions_framework.cloud_event
def gcs_trigger_func(cloud_event):
    data = cloud_event.data
    bucket = data["bucket"]
    name = data["name"]
    gcs_link = f"gs://{bucket}/{name}"

    api_endpoint = f"https://dashboard.ccai.pawait.io/api/v1/transcribe?gs_link={gcs_link}"
    
    return requests.get(api_endpoint)
