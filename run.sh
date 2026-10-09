#!/bin/sh
# Build and run Dave's Message Board on http://localhost:8082/
set -e
cd "$(dirname "$0")"
docker compose up -d --build
echo "The board is starting on http://localhost:8082/   (moderator password: letmein)"
echo "Logs:  docker compose logs -f"
echo "Stop:  docker compose down -v"
