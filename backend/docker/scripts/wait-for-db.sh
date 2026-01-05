#!/bin/bash
# Wait for PostgreSQL to be ready before proceeding

set -e

host="$1"
shift
cmd="$@"

until pg_isready -h "$host" -U "postgres"; do
  >&2 echo "PostgreSQL is unavailable - sleeping"
  sleep 1
done

>&2 echo "PostgreSQL is up - executing command"
exec $cmd
