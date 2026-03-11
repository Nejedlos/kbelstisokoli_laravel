# External Data and Player Mapping

The KS system is not an isolated island. To automate results, standings, and statistics, it regularly connects to external servers (particularly the ČBF portal). For this synchronization to function, "Mapping" must be correctly configured.

### Entity Mappings
Mapping is the technical bridge between our system and a third-party system.
- **Teams:** You must link our team (e.g., U15) with its corresponding ID in the ČBF system.
- **Players:** Every player who should have statistics on the website must have their `external_id` (e.g., ID from the cz.basketball portal) configured in the "Mappings" section.
- **Matches:** The system automatically pairs matches using unique federation codes.

### Statistic Sources
In this section, you define where the system downloads data from.
- **API URL:** The address where the data is located.
- **API Key:** The access password for secure data retrieval.
- **Sync Intervals:** Determines how frequently data should be updated (e.g., every hour during the season, once a day in summer).

### Resolving Mismatches
Sometimes synchronization encounters an issue:
- **Name Mismatch:** A player's name is recorded differently in our system than in ČBF (e.g., diacritics). The system reports a "Mismatch," and you must manually confirm it is the same person.
- **Duplicate ID:** Two players share the same external ID. This is a critical error that must be corrected in the user's details.

### Manual Sync Enforcement
If you need data immediately (e.g., after an important match concludes):
1. Navigate to the **External Sources** section.
2. Use the **Run Sync Now** action.
3. Monitor progress in the **Import Logs** section.

### Frequently Asked Questions
- **Why aren't a player's points loading?** Check if the player has the correct `external_id` set and if their team is mapped within the given competition.
- **Where do I find a player's ID in ČBF?** Usually in the URL address of the player's profile on the cz.basketball website.
