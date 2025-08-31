#!/bin/bash

cd /cygdrive/c/Users/Matt/marvellousmainecoons || { echo "❌ Repo path not found"; exit 1; }

timestamp=$(date +"%Y-%m-%d %H:%M:%S %Z")
branch=$(git rev-parse --abbrev-ref HEAD)

# Stage and commit changes
git add .
git commit -m "Automated commit – $timestamp"
git push origin "$branch"

if [ $? -eq 0 ]; then
    echo "✅ Push to '$branch' successful at $timestamp"
else
    echo "❌ Push failed – check remote or branch status"
    exit 1
fi

# If on dev, merge into main and push main
if [ "$branch" = "dev" ]; then
    echo "🔄 Merging 'dev' into 'main'..."
    git checkout main || { echo "❌ Failed to switch to 'main'"; exit 1; }
    git merge dev || { echo "❌ Merge failed"; exit 1; }
    git push origin main

    if [ $? -eq 0 ]; then
        echo "✅ 'main' updated with 'dev' changes at $timestamp"
    else
        echo "❌ Push to 'main' failed – check merge conflicts or remote status"
    fi
fi