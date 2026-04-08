UPDATE Leave 
   SET Leave.ScheduledPersonID=sp.ScheduledPersonID
   FROM Leave  INNER JOIN  StaffDetails sd ON Leave.StaffNumber = sd.StaffNumber
   INNER JOIN  ScheduledPeople sp on sd.StaffID = sp.StaffDetailsID