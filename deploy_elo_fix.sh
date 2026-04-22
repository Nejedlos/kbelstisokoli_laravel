#!/bin/bash
REMOTE_PATH="/home/html/kbelstisokoli.cz/public_html/secret"
SSH_CMD="ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -p 20001 ssh-588875@dw191.webglobe.com"
upload_file() {
    local local_file=$1
    local remote_file=$2
    cat "$local_file" | $SSH_CMD "cat > $REMOTE_PATH/$remote_file"
}
upload_file "app/Models/TeamEloRating.php" "app/Models/TeamEloRating.php"
upload_file "app/Console/Commands/RecomputeEloRatings.php" "app/Console/Commands/RecomputeEloRatings.php"
$SSH_CMD "cd $REMOTE_PATH && php8.4 artisan stats:elo:recompute"
