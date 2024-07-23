# Set this to current python environment
#! /opt/conda/bin/python

import subprocess
import time
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
from google.cloud import storage
import sys

WAIT_TIME = 1


class GCSHandler(FileSystemEventHandler):
    def __init__(self, local_directory, bucket_name):
        self.local_directory = local_directory
        self.bucket_name = bucket_name
        self.client = storage.Client()
        self.bucket = self.client.bucket(bucket_name)
        self.sync_to_gcs(local_directory)

    def sync_to_gcs(self, src_path):
        cmd = [
            "gsutil",
            "-m",
            "rsync",
            "-d",
            self.local_directory,
            f"gs://{self.bucket_name}",
        ]
        subprocess.run(cmd)

    def on_created(self, event):
        if not event.is_directory:
            print(f"File created: {event.src_path}")
            self.sync_to_gcs(event.src_path)

    def on_modified(self, event):
        if not event.is_directory:
            print(f"File modified: {event.src_path}")
            self.sync_to_gcs(event.src_path)

    def on_deleted(self, event):
        if not event.is_directory:
            print(f"File deleted: {event.src_path}")
            self.sync_to_gcs(event.src_path)


if __name__ == "__main__":
    PATH = sys.argv[1]
    BUCKET_NAME = sys.argv[2]

    event_handler = GCSHandler(PATH, BUCKET_NAME)
    observer = Observer()
    observer.schedule(event_handler, PATH, recursive=True)

    print("START => Starting to monitor the directory for changes...")
    observer.start()

    try:
        while True:
            time.sleep(WAIT_TIME)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()
