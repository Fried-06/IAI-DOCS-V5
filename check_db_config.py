import sqlite3
import psycopg2
import json

# Checking how subjects are stored in the database
with open("backend/db.php", "r") as f:
    print(f.read())
