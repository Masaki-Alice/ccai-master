FROM python:3.12
WORKDIR /code

COPY ./ /code

RUN pip install --upgrade pip
RUN pip install poetry

RUN poetry update
CMD ["poetry", "run", "uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "80", "--reload"]