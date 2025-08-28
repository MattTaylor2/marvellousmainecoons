#!/bin/bash

AUDIT_LOG="/var/log/marvellous/rotation_audit.log"
HASH_LOG="/var/log/marvellous/audit_hashes.log"
MARKER_FILE="/tmp/logrotate_hook_ran"
LOG_TAG="logrotate_hook"
LOG_FILE="/var/log/marvellous/admin.log"

# Ensure script is run by logrotate
if [ -z "$LOGROTATE" ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - ERROR: Called outside logrotate" >> "$AUDIT_LOG"
    logger -t "$LOG_TAG" "ERROR: logrotate_hook.sh called outside logrotate"
    exit 1
fi

# Log rotation event
echo "$(date '+%Y-%m-%d %H:%M:%S') - admin.log rotated by logrotate" >> "$AUDIT_LOG"
logger -t "$LOG_TAG" "admin.log rotated"

# Create marker file
if touch "$MARKER_FILE"; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Marker file created: $MARKER_FILE" >> "$AUDIT_LOG"
else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - ERROR: Failed to create marker file" >> "$AUDIT_LOG"
    logger -t "$LOG_TAG" "ERROR: Failed to create marker file"
    exit 2
fi

# Hash the most recent rotated log
ROTATED=$(ls -1t /var/log/marvellous/admin.log.*.gz 2>/dev/null | head -n 1)
if [ -n "$ROTATED" ]; then
    HASH=$(sha256sum "$ROTATED" | awk '{print $1}')
    echo "$(date '+%Y-%m-%d %H:%M:%S') - SHA256 $ROTATED: $HASH" >> "$HASH_LOG"
    logger -t "$LOG_TAG" "SHA256 hash logged for $ROTATED"
else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - WARNING: No rotated admin.log found to hash" >> "$AUDIT_LOG"
    logger -t "$LOG_TAG" "WARNING: No rotated admin.log found to hash"
fi

exit 0

