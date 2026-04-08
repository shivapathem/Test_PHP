Update ScheduledPeople Set DisplayFirstName  = CASE WHEN CHARINDEX(' ', DisplayName) > 0 then SUBSTRING(SUBSTRING(DisplayName, 1, CHARINDEX(' ', DisplayName) - 1),1,25) else DisplayName END,
DisplayLastName = CASE WHEN CHARINDEX(' ', DisplayName) > 0 then SUBSTRING(SUBSTRING(DisplayName,
                 CHARINDEX(' ', DisplayName) + 1,
                 LEN(DisplayName)+1 - CHARINDEX(' ', DisplayName)),1,25)  else ' ' END