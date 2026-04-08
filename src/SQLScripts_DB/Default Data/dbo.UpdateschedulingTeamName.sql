USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
UPDATE schedulingTeams set schedulingTeamName = 'Archive' where schedulingTeamName = 'No Team'