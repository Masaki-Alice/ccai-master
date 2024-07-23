import requests
import google.auth
from google.auth.transport.requests import Request
from google.cloud import contact_center_insights_v1
from google.longrunning import operations_pb2
import time


def get_operation(operation_name: str) -> operations_pb2.Operation:
    insights_client = contact_center_insights_v1.ContactCenterInsightsClient()
    operation = insights_client.transport.operations_client.get_operation(
        operation_name
    )
    return operation


def poll_operation(operation_name: str, interval: int = 30) -> None:
    while True:
        operation = get_operation(operation_name)
        if operation.done:
            print("Operation is done")
            break
        else:
            print("Operation is in progress")
            time.sleep(interval)


def bulk_analyze() -> str:
    # Authenticate and get the access token
    credentials, project_id = google.auth.default()
    credentials.refresh(Request())
    access_token = credentials.token

    # Define the request URL
    url = "https://contactcenterinsights.googleapis.com/v1/projects/ccai-dev-project/locations/us-central1/conversations:bulkAnalyze"

    # Define the headers
    headers = {
        "Authorization": f"Bearer {access_token}",
        "Content-Type": "application/json; charset=utf-8"
    }

    # Define the JSON body for the request
    data = {
        "analysisPercentage": 100,
        "filter": "-latest_analysis:*"
    }

    # Send the POST request
    response = requests.post(url, headers=headers, json=data)

    # Check the response
    if response.status_code == 200:
        print("Bulk analysis request successful")
        operation_name = response.json().get('name')
        if operation_name:
            print(f"Bulk Analyze Operation Name: {operation_name}")
            return operation_name
        else:
            print("Operation name not found in the response")
            return None
    else:
        print("Bulk analysis request failed")
        print(response.status_code)
        print(response.text)
        return None


def export_insights_data() -> str:
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
        print("Export request successful")
        operation_name = response.json().get('name')
        if operation_name:
            print(f"Export Operation Name: {operation_name}")
            return operation_name
        else:
            print("Operation name not found in the response")
            return None
    else:
        print("Export request failed")
        print(response.status_code)
        print(response.text)
        return None


def ccai_upload():
    # Authenticate and get the access token
    credentials, project_id = google.auth.default()
    credentials.refresh(Request())
    access_token = credentials.token

    # Define the request URL for conversation ingestion
    url = "https://contactcenterinsights.googleapis.com/v1/projects/ccai-dev-project/locations/us-central1/conversations:ingest"

    # Define the headers
    headers = {
        "Authorization": f"Bearer {access_token}",
        "Content-Type": "application/json; charset=utf-8"
    }

    # Define the JSON body for the request
    data = {
        "gcsSource": {
            "bucketUri": "gs://sample-insights-conversations/agent-1/",
            "bucketObjectType": "TRANSCRIPT"
        },
        "transcriptObjectConfig": {
            "medium": "CHAT"
        }
    }

    # Send the POST request for conversation ingestion
    response = requests.post(url, headers=headers, json=data)

    # Check the response
    if response.status_code == 200:
        print("Request successful")
        operation_name = response.json().get('name')
        if operation_name:
            print(f"Ingest Operation Name: {operation_name}")
            # Poll the ingest operation
            poll_operation(operation_name)

            # Perform bulk analysis
            bulk_analyze_operation_name = bulk_analyze()
            if bulk_analyze_operation_name:
                # Poll the bulk analyze operation
                poll_operation(bulk_analyze_operation_name)

                # Export insights data
                export_operation_name = export_insights_data()
                if export_operation_name:
                    # Poll the export operation
                    poll_operation(export_operation_name)
        else:
            print("Operation name not found in the response")
    else:
        print("Request failed")
        print(response.status_code)
        print(response.text)


if __name__ == "__main__":
    ccai_upload()
