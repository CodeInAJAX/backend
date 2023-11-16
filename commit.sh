#!/bin/bash

git config --global user.email "awebthp@gmail.com"
git config --global user.name "tirtahakimpambudhi"

WORKDIR=$(pwd)
DEFAULT_DATE="2024-06-03"  # Set your desired start date
read -p "Enter Start Date (YYYY-MM-DD) : "  START_DATE

# Validate START_DATE
if [[ -z "$START_DATE" ]]; then
  # If START_DATE is empty, use DEFAULT_DATE
  START_DATE="$DEFAULT_DATE"
elif [[ ! "$START_DATE" =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]]; then
  # If START_DATE is not in the format YYYY-MM-DD, use DEFAULT_DATE
  echo "Invalid date format. Using default date: $DEFAULT_DATE"
  START_DATE="$DEFAULT_DATE"
else
  # Additional validation for logical correctness (e.g., valid day/month)
  if ! date -d "$START_DATE" >/dev/null 2>&1; then
    echo "Invalid date value. Using default date: $DEFAULT_DATE"
    START_DATE="$DEFAULT_DATE"
  fi
fi

# Function to check if file is in .gitignore
is_ignored() {
    local file="$1"
    if git check-ignore -q "$file"; then
        return 0  # File is ignored
    fi
    return 1  # File is not ignored
}

# Function to validate if file can be added to git
can_be_added() {
    local file="$1"
    # Try to add the file
    if git add "$file" 2>/dev/null; then
        # If successful, reset the add
        git reset "$file" >/dev/null 2>&1
        return 0  # File can be added
    else
        return 1  # File cannot be added
    fi
}

# Function to check if file is already committed
is_committed() {
    local file="$1"
    # Check if file is tracked and has no changes
    if git ls-files --error-unmatch "$file" >/dev/null 2>&1; then
        if ! git diff --quiet HEAD "$file" 2>/dev/null; then
            return 1  # File has changes
        fi
        return 0  # File is committed and unchanged
    fi
    return 1  # File is not committed
}

# Function to check file status (untracked/modified)
get_file_status() {
    local file="$1"
    if git ls-files --error-unmatch "$file" >/dev/null 2>&1; then
        echo "modified"
    else
        echo "untracked"
    fi
}

stringContain() { case $2 in *$1* ) return 0;; *) return 1;; esac ;}

# Function to generate commit message based on directory
get_commit_message() {
    local file="$1"
    local status="$2"
    local dir=$(dirname "$file")
    local basename=$(basename "$file")
    local extension="${file##*.}"
    local action="Add"

    if [ "$status" = "modified" ]; then
        action="Update"
    fi

    if stringContain "md" "$extension" ;then
       local context_docs=$(basename "$dir")
       echo "docs($context_docs): :memo: $action documentation in file $basename"
       return
    fi

    if stringContain "ignore" "$file"; then
      echo "docs(ignore): :see_no_evil: $action file ignore $basename"
      return
    fi


    if stringContain "index.js" "$file"; then
      echo "feat(main): :sparkles: $action feature in file $basename"
      return
    fi

    if stringContain "docker" "$file" || stringContain "Docker" "$file";then
      echo "chore(docker): :package: $action docker tools in file $basename"
      return
    fi

    if stringContain "package" "$file" ; then
        echo "chore(package): :package: $action $basename file for manage dependencies library"
        return
    fi


    if [[ "$basename" == .env* ]]; then
      echo "feat(config): :wrench: $action environment example to required in the app"
      return
    fi

    if [[ "$basename" == eslint* ]]; then
      echo "style(lint): :rotating_light: $action linter configuration file"
      return
    fi

    if [[ "$basename" == .prettierrc ]]; then
      echo "style(prettier): :rotating_light: $action format configuration file"
      return
    fi

    if stringContain "spec" "$file"; then
      echo "test: :white_check_mark: $action unit tests in the file $basename"
      return
    fi


    case "$dir" in
        *"/src"*|*"/pkg"*)
            local context_feat=$(basename "$dir")
            echo "feat($context_feat): :sparkles: $action $context_feat in the file $basename"
            ;;
        *"/.github"*)
            echo "ci: :green_heart: $action CI/CD in the file $basename"
            ;;
        *)
        echo "chore: $action $basename files"
            ;;
    esac
}

# Function to get user input for commit message and body
get_user_commit_message() {
    local default_msg="$1"
    local commit_msg
    local commit_body

    # Prompt for commit message
    read -p "Enter commit message (default: $default_msg): " commit_msg
    commit_msg=${commit_msg:-$default_msg}

    # Prompt for commit body
    read -p "Do you want to add a commit body? (y/n): " add_body
    if [[ "$add_body" =~ ^[Yy]$ ]]; then
        echo "Enter commit body (end with an empty line):"
        commit_body=""
        while IFS= read -r line; do
            [[ -z "$line" ]] && break
            commit_body+="$line"$'\n'
        done
    fi

    # Return the commit message and body
    echo -e "$commit_msg\n$commit_body"
}

# Function to get next date
get_next_date() {
    local current_date="$1"
    date -d "$current_date + 1 day" +%Y-%m-%d
}

# Initialize current date
current_date="$START_DATE"

# Enable dotglob to include hidden files
shopt -s globstar dotglob

# Process files
for fileCommit in ./**/*; do
    # Skip if file is a directory or . or ..
    [[ -d "$fileCommit" ]] && continue
    [[ "$fileCommit" == "./." ]] && continue
    [[ "$fileCommit" == "./.." ]] && continue

    # Skip if file is in .gitignore
    if is_ignored "$fileCommit"; then
        continue
    fi

    # Skip if file is already committed
    if is_committed "$fileCommit"; then
        echo "Skipping already committed file: $fileCommit"
        continue
    fi

    # Skip if file cannot be added to git
    if ! can_be_added "$fileCommit"; then
        echo "Warning: Cannot add file to git: $fileCommit"
        continue
    fi

    # Get file status
    file_status=$(get_file_status "$fileCommit")

    # Get commit message based on directory and status
    default_commit_msg=$(get_commit_message "$fileCommit" "$file_status")
    user_commit_msg="$default_commit_msg"

    # Add and commit the file with the generated message and date
    if git add "$fileCommit"; then
        GIT_AUTHOR_DATE="$current_date 12:00:00" \
        GIT_COMMITTER_DATE="$current_date 12:00:00" \
        git commit -m "$(echo "$user_commit_msg" | head -n 1)" -m "$(echo "$user_commit_msg" | tail -n +2)" --date="$current_date" "$fileCommit"

        echo "Committed $fileCommit ($file_status) on $current_date with message: $(echo "$user_commit_msg" | head -n 1)"

        # Move to next day
        current_date=$(get_next_date "$current_date")
    else
        echo "Error: Failed to add file: $fileCommit"
        continue
    fi
done
