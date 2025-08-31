#!/bin/bash

cd /cygdrive/c/Users/Matt/marvellousmainecoons || { echo "❌ Repo path not found"; exit 1; }

timestamp=$(date +"%Y-%m-%d %H:%M:%S %Z")

git add .
git commit -m "Automated commit – $timestamp"
git push origin main

if [ $? -eq 0 ]; then
    echo "✅ Push successful at $timestamp"
    else
        echo "❌ Push failed – check remote URL or credentials"
	fi
