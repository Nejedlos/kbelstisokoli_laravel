Detailed member editing (People & Members > Users > selected user) is divided into several logical tabs for clarity and easy management of a large amount of data. In the header, you always see a summary card with key data (ID, VS, Status).

### Overview and Identity Tab
Serves to manage basic identity and contacts.
- **Avatar**: Profile photo. By clicking on the photo in the header or in the tab, new versions can be uploaded (e.g., after seasonal portrait sessions).
- **Display Name**: If filled, the system prefers it on the website over the combination of first name + last name (e.g., for a shortened nickname).
- **Phone Numbers**: You can enter both main and secondary contacts (it automatically adds the +420 prefix).

### Personal and Address Tab
- **Date of Birth**: Key data for automatic classification into age categories.
- **Emergency Contact**: Name and phone number of the person the coach should call in case of injury during training. **Important for children.**
- **Address**: Serves for official correspondence and is required for administration related to registration in ČBF and Sokol.

### Club Data Tab
- **Member ID and Payment VS**: Unique identifiers in the club. If they are not filled for a new member, they can be generated using the "refresh" icon in the field. Once generated, the ID can no longer be changed.
- **Membership Status**: Determines whether the membership is active, pending, suspended, or former.
- **Membership Types**: Multiple values can be selected, for example “Player” and “Coach”. Player, Coach and Parent automatically assign the corresponding access roles.
- **Finance OK**: Quick indicator (toggle) whether the member has all club obligations in order (set manually by agreement or automatically from the financial module).
- **Recommended Payment Method**: Choice between bank transfer, cash, etc. (affects information for users in the member section).

### Player Data Tab
This section is only displayed if the "Has active player profile" toggle is on.
- **Basketball**: Evidence of jersey number, ČBF license, and game position. You can specify a "Primary Team" here, which is displayed with the name in lists.
- **Physical Parameters**: Height (cm), weight (kg) (for coaches) and equipment size (jersey/shorts) for orders.
- **Internal Section**: Contains hidden coach notes and medical notes (e.g., allergies, asthma, vision defects) that the regular user does not see.
- **Player Gallery**: Uploading photos from matches. The first photo in the grid is taken as primary for the team roster.

### Security Tab
- **Password Management**: Administrators can set a new password (dehydrated, i.e., it only changes when filled).
- **2FA (Two-Factor Authentication)**: You can see the user's security status here. If the user cannot log in due to losing their phone, 2FA can be reset (disabled) here.
- **Account Activation**: In the Security tab (during Editing), there is also an action for quick activation/deactivation of the entire access.

### Admin Tab
- **User Roles**: Shows the resulting access rights. Player, Coach and Parent roles are synchronized from membership types on the Club tab. Administrative roles such as Administrator or Editor are assigned here and are preserved by automatic synchronization.
- **Admin Note**: Internal text space for system administrators (e.g., history of issues, transfer notes).
- **Audit**: Overview of creation, modification, and last login dates.

### Common Procedures
- **Creating a new player**: Create a user, generate ID and VS on the Club tab, turn on "Active player profile" and fill in the game position on the Player tab.
- **Member departure**: On the Club tab, set the "Membership end date" and deactivate the user on the Security tab.
