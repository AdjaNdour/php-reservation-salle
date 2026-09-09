#!/bin/sh
set -e

IMAGE="adjacourandour/php-reservation-salle"
REPO_ROOT=$(pwd)
WORKTREE_DIR="/tmp/reservation-salle-release"

REUSSIS=""
IGNORES=""

rm -rf "$WORKTREE_DIR"

for TAG in $(git tag --sort=version:refname); do
    echo ""
    echo "======================================"
    echo "=== Construction de ${TAG} ==="
    echo "======================================"

    # Récupérer le code correspondant au tag Git
    git worktree add --detach "$WORKTREE_DIR" "$TAG" > /dev/null 2>&1

    # Utiliser le Dockerfile actuel
    cp "$REPO_ROOT/Dockerfile" "$WORKTREE_DIR/Dockerfile"

    # Vérifier que composer.json existe
    if [ ! -f "$WORKTREE_DIR/composer.json" ]; then
        echo "Ignore : composer.json absent pour ${TAG}."
        IGNORES="${IGNORES} ${TAG}"

        git worktree remove --force "$WORKTREE_DIR"
        continue
    fi

    echo "Build Docker : ${IMAGE}:${TAG}"

    if ! (cd "$WORKTREE_DIR" && \
        docker build -t "${IMAGE}:${TAG}" .); then

        echo "Echec du build pour ${TAG}."
        IGNORES="${IGNORES} ${TAG}"

        git worktree remove --force "$WORKTREE_DIR"
        continue
    fi

    echo "Push Docker Hub : ${IMAGE}:${TAG}"

    if ! docker push "${IMAGE}:${TAG}"; then
        echo "Echec du push pour ${TAG}."
        IGNORES="${IGNORES} ${TAG}"

        git worktree remove --force "$WORKTREE_DIR"
        continue
    fi

    echo "SUCCES : ${TAG}"

    REUSSIS="${REUSSIS} ${TAG}"

    git worktree remove --force "$WORKTREE_DIR"
done

# Le dernier tag Git devient latest
DERNIER_TAG=$(echo "$REUSSIS" | tr ' ' '\n' | tail -1)

if [ -n "$DERNIER_TAG" ]; then

    echo ""
    echo "======================================"
    echo "=== Création du tag latest ==="
    echo "======================================"

    docker tag \
        "${IMAGE}:${DERNIER_TAG}" \
        "${IMAGE}:latest"

    docker push "${IMAGE}:latest"

    echo "SUCCES : latest → ${DERNIER_TAG}"
fi

echo ""
echo "======================================"
echo "=== RESUME ==="
echo "======================================"

echo "Images poussées :${REUSSIS}"
echo "Tags ignorés    :${IGNORES}"
echo "Dernier tag     : ${DERNIER_TAG}"
