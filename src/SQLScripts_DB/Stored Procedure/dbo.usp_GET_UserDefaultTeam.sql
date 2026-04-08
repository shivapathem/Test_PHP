USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_UserDefaultTeam]    Script Date: 21/04/2022 16:01:34 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_GET_UserDefaultTeam]
@scheduledPersonId INT
AS
BEGIN
	SET NOCOUNT ON;
    SELECT ScheduledPersonTeam_LINK.TeamID
	FROM ScheduledPeople (nolock)
	INNER JOIN ScheduledPersonTeam_LINK (nolock) on ScheduledPeople.ScheduledPersonID=ScheduledPersonTeam_LINK.ScheduledPersonID			
	WHERE ScheduledPeople.ScheduledPersonID=@scheduledPersonId and ScheduledPersonTeam_LINK.isDefault=1
END
