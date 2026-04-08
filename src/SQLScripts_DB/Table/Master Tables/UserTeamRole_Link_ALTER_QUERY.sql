USE [Allocate7]
GO

/****** Object:  Table [dbo].[Allocations]    Script Date: 27/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'StartDate'
          AND Object_ID = Object_ID(N'[dbo].[UserTeamRole_LINK]'))
BEGIN
ALTER table [dbo].[UserTeamRole_LINK] add StartDate datetime NULL
END
IF NOT EXISTS(SELECT 1 FROM sys.columns
          WHERE Name = N'EndDate'
          AND Object_ID = Object_ID(N'[dbo].[UserTeamRole_LINK]'))
BEGIN
ALTER table [dbo].[UserTeamRole_LINK] add EndDate datetime NULL
END
update T1 set T1.StartDate = sptl.StartDate,T1.EndDate = isnull(sptl.EndDate,'9999-12-01') from UserTeamRole_LINK T1
	inner join ScheduledPeople sp on sp.UserID = T1.UserID
    inner join ScheduledPersonTeam_LINK sptl on sptl.ScheduledPersonID = sp.ScheduledPersonID and sptl.TeamID = T1.TeamID and Scheduledtype = 0
where isnull(T1.StartDate, '') = ''
GO
