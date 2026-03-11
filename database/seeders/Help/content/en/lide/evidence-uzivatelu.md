This section (People & Members > Users) is the cornerstone for managing the entire membership base. It allows for quick member lookup, active status monitoring, and performing bulk operations.

### Table Overview and Visual Indicators
In the main table, you can see all registered individuals. The system automatically analyzes data and highlights important states using icons in the name row:

- **<i class="fa-light fa-ghost text-gray-400"></i> Ghost Icon**: Indicates a user who has been created in the system (e.g., by import) but has never logged in and completed the "onboarding" process. These profiles are often duplicates of real accounts.
- **<i class="fa-light fa-circle-exclamation text-warning"></i> Exclamation Icon**: Indicates a detected duplicate. The system has found another record with the same name. The number in brackets shows the count of duplicates.
- **<i class="fa-light fa-cloud-arrow-down text-info"></i> Cloud Icon**: Marks a user whose data is synchronized from an external source (e.g., cz.basketball).
- **Active/Inactive Status**: A color badge determining whether the user currently has access to their member section. Inactive users cannot log in.

### Advanced Filtering
For efficient management of thousands of members, use combined filters:

1. **By Role**: Show only "Coaches", "Administrators", or "Players".
2. **By Team**: Key filter for coaches. Shows members assigned to a specific team in the current season.
3. **2FA Status**: Allows finding users who do not yet have active two-factor authentication (critical for administrators).
4. **Player Profile**: Quickly filters out individuals who are in the system only as companions (parents) or officials without gaming history.
5. **Duplicates**: A special filter that hides all unique records and leaves only those where a name match exists – ideal for database cleanup.

### Bulk Actions
On the left side of the table, you can select multiple users and use bulk actions in the header:

- **Activate / Deactivate**: Bulk toggle of access (e.g., after the season ends).
- **Synchronize from cz.basketball**: Bulk update of statistics and licenses for selected players.
- **Merge Ghosts Automatically**: Safe bulk merging of temporary profiles with their real counterparts (if name match is 1:1).

### Key Administrative Actions
- **Merge with...**: If you find a duplicate, use this action to transfer all data (statistics, payments, relationships) to one main profile and delete the other (redundant) one. **Caution: This operation is irreversible.**
- **Show Duplicates**: A quick action that switches you to searching for all individuals with the same name so you can compare them before merging.
- **Send Invitation**: Sends an email with a link to set a password. Use for new members or when resetting access.

### Impacts and Relationships
Any change in user status in this section has an immediate chain effect:

- **Access**: Deactivating a user immediately cancels all their active logins (including the mobile app).
- **Rosters and Nominations**: Inactive users or users without a player profile are not offered in lists for matches and trainings.
- **Financial Module**: Deactivation stops the automatic generation of new payment prescriptions.
