USE [Allocate7]

GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON

GO
IF NOT EXISTS(SELECT 1 FROM sys.columns WHERE Name = N'isWhosIn' AND Object_ID = Object_ID(N'[dbo].[ScheduledPersonTeam_LINK]'))
BEGIN
	alter table ScheduledPersonTeam_LINK add isWhosIn tinyint not null default 0
END

GO