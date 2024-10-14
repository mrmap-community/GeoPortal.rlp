################################
# Base build Image
################################  
FROM python:3.11.7-alpine3.18 AS compile-image
RUN apk update && \
    apk add --no-cache postgresql-dev musl-dev gcc build-base

ENV VIRTUAL_ENV=/opt/venv
RUN python3 -m venv $VIRTUAL_ENV
ENV PATH="$VIRTUAL_ENV/bin:$PATH"

# build python dependencies    
COPY requirements.txt requirements.txt
RUN /usr/local/bin/python -m pip install --upgrade pip && \
    pip install -r requirements.txt

################################
# GeoPortal Image
################################    
FROM python:3.11.7-alpine3.18 AS runtime-image
COPY --from=compile-image /opt/venv /opt/venv

# TODO: gettext are only needed for dev environment
RUN apk update \
    && apk add --no-cache libpq netcat-openbsd gettext libressl py3-psycopg \
    && rm -rf /var/cache/apk/*

# set work directory
WORKDIR /opt/geoportal

# set environment variables
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1
ENV PATH="/opt/venv/bin:$PATH"
ENV CONTAINER="1"


ENTRYPOINT [ "/opt/geoportal/docker/geoportal/entrypoint.sh" ]

CMD [ "/opt/geoportal/docker/geoportal/startup.sh" ]

EXPOSE 8007/tcp
