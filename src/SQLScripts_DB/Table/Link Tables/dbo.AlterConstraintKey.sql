USE [Allocate7]
GO

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

ALTER TABLE [dbo].[UserTeamRole_LINK] DROP  CONSTRAINT [FK_UserTeamRole_Team] 

ALTER TABLE [dbo].[UserTeamRole_LINK]  WITH CHECK ADD  CONSTRAINT [FK_UserTeamRole_Team] FOREIGN KEY([TeamID])
REFERENCES [dbo].[schedulingTeams] ([schedulingTeamId])


