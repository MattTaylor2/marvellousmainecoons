#!/bin/bash

cd /cygdrive/c/Users/Matt/marvellousmainecoons || { echo "❌ Repo path not found"; exit 1; }

timestamp=$(date +"%Y-%m-%d %H:%M:%S %Z")

# Detect current branch
branch=$(git rev-parse --abbrev-ref HEAD)

git add .
git commit -m "Automated commit – $timestamp"
git push origin "$branch"

if [ $? -eq 0 ]; then
    echo "✅ Push to '$branch' successful at $timestamp"
else
    echo "❌ Push failed – check remote or branch status"
fi
