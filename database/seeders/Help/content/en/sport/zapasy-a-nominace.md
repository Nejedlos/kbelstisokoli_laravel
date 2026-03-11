The **Matches** section covers the complete lifecycle of a game – from automatic scheduling to recording detailed statistics and results.

### Automation: Sync with cz.basketball (ČBF)
This function is essential for the Kbelští sokoli club and saves hours of administrative work.
- **How it works**: If a team plays a competition under the ČBF umbrella, the system automatically connects to their API and regularly downloads the schedule and results of matches.
- **External Indicator**: In the matches table, you can recognize a synchronized match by the blue <i class="fa-light fa-cloud-arrow-down text-info"></i> **Cloud icon** at the date of the match.
- **Bulk AI Synchronization**: If details (points, fouls of players) are missing for historical matches, you can select matches in bulk actions and run "AI Synchronization of Details". It will attempt to automatically "read" and assign statistics to players in the database.

### Planning and Match Management
For friendly matches or tournaments that are not in ČBF, you must create the match manually.
1. **Basic Data**: Selection of home team, opponent (if missing, create them in the Opponents section), date, time, and location.
2. **Venue (Hall)**: You can specify a particular hall for each match. If it's the Kbely hall, it's automatically offered with a map.
3. **Match Status**: Planned (created), Scheduled (term confirmed), Played (result recorded), Postponed/Cancelled.

### Nominations and Player Attendance
This process takes place in two phases (Invitation and Reality):
- **Nomination**: The coach selects players from the team roster in the match detail (Attendance tab). Using a bulk action, they send the invitation.
- **Player's Response**: Players receive a notification in the mobile app and must confirm participation (`confirmed`) or apologize (`declined`).
- **Reality (Actual Status)**: After the match, the coach updates the attendance to "Present" (`attended`) or "Absent" (`absent`).

### Results and Statistics (Reports)
- **Score**: After the match, enter the final score. The system automatically evaluates the win/loss and colors the result in the table.
- **Points and Fouls**: If the match is synchronized from ČBF, statistics are loaded automatically. If not, they can be recorded manually on the match roster.
- **Links**: We recommend inserting a link to the "ČBF Technical Record" for each match so that players have easy access to it.

### Tips and Troubleshooting
- **Time Change**: If the match time changes in ČBF, the system will automatically update it during the next synchronization.
- **Attendance Mismatch**: If a player confirmed participation but did not show up, the system displays a **red badge with the count of discrepancies** (Mismatches) in the matches table. This is a signal for the coach to verify attendance.
