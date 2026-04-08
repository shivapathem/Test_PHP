USE [Allocate7]
GO


CREATE NONCLUSTERED INDEX [idx_vRotaDutyDays_DutyTypeID]  ON [dbo].[vRotaDutyDays]
  ( 
    [DutyTypeID] ASC, 
    [IsActive] ASC
  )
INCLUDE
  ( RotaID, 
    RotaWeekLine, 
	DayOfRota, 
	MasterDutyID
  ) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
GO

CREATE NONCLUSTERED INDEX [idx_StaffDetails_NetLogin]  ON [dbo].[StaffDetails]
  ( 
    [NetLogin] ASC
  )
INCLUDE
  ( StaffID, 
    StaffNumber, 
	Surname, 
	Forename
  ) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
GO
 
CREATE NONCLUSTERED INDEX [idx_ScheduledPersonTeam_LINK_TeamID]  ON [dbo].[ScheduledPersonTeam_LINK]
  ( 
    [TeamID] ASC,
    [IsHomeTeam] ASC,
    [scheduledType] ASC	
  )
INCLUDE
  ( 
   ScheduledPersonID
  ) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
GO
 
CREATE NONCLUSTERED INDEX [idx_ScheduledPersonTeam_LINK_ScheduledPersonID]  ON [dbo].[ScheduledPersonTeam_LINK]
  ( 
    [ScheduledPersonID] ASC,
    [IsHomeTeam] ASC,
    [EndDate] ASC	
  )
 WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
GO


CREATE NONCLUSTERED INDEX [idx_UserTeamRole_LINK_UserID]  ON [dbo].[UserTeamRole_LINK]
  ( 
    [UserID] ASC
  )
INCLUDE
  ( 
   TeamID,
   RoleID
  ) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON)
GO