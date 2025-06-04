import os
import pytest
from bs4 import BeautifulSoup

BASE_DIR = os.path.dirname(os.path.dirname(__file__))

TEST_CASES = [
    ("index.html", ["searchInput"]),
    ("fonologi.html", ["startButton"]),
    ("pengenalan-fonologi.html", ["startButton"]),
    ("hasil.html", ["userName"]),
    ("hasil2.html", ["userName"]),
    ("ujian-lisan.html", ["recordButton"]),
    ("ujian-lisan2.html", ["recordButton"]),
    ("ujian-tulis.html", ["answerInput"]),
]

@pytest.mark.parametrize("filename, ids", TEST_CASES)
def test_html_contains_elements(filename, ids):
    path = os.path.join(BASE_DIR, filename)
    assert os.path.exists(path), f"File {filename} does not exist"
    with open(path, encoding="utf-8") as f:
        soup = BeautifulSoup(f, "html.parser")
    for element_id in ids:
        assert soup.find(id=element_id) is not None, f"Missing element {element_id} in {filename}"

