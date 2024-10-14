from json import JSONDecodeError

import requests
from requests import Session
from requests.exceptions import ConnectionError
from requests.packages.urllib3.exceptions import InsecureRequestWarning

requests.packages.urllib3.disable_warnings(InsecureRequestWarning)


class CustomSession(Session):
    """ConnectionError and JSONDecodeError safe request handling"""

    def __init__(self) -> None:
        super().__init__()
        # TODO: add proxy settings

    def get(self, url, **kwargs):
        try:
            response = super().get(url, **kwargs)
            if response.status_code == 200:
                response = response.json()
            else:
                response = {}
        except (ConnectionError, JSONDecodeError) as e:

            response = {}
        return response
