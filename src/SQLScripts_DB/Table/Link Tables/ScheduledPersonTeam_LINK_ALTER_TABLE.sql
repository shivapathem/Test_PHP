USE [Allocate7]
GO

/****** Object:  Table [dbo].[ScheduledPersonTeam_LINK]    Script Date: 02/07/2021 15:00:23 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[isDefault]'
          AND Object_ID = Object_ID(N'[dbo].[ScheduledPersonTeam_LINK]'))
BEGIN
	ALTER TABLE [dbo].ScheduledPersonTeam_LINK ADD isDefault tinyint NOT NULL CONSTRAINT [DF_ScheduledPersonTeam_LINK_isDefault]  DEFAULT ((0))
				WITH VALUES;

EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'1= default team role ,0- non default' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'ScheduledPersonTeam_LINK', @level2type=N'COLUMN',@level2name=N'isDefault'

END


IF NOT EXISTS(SELECT 1 FROM sys.columns 
          WHERE Name = N'[scheduledType]'
          AND Object_ID = Object_ID(N'[dbo].[ScheduledPersonTeam_LINK]'))
BEGIN
	ALTER TABLE [dbo].ScheduledPersonTeam_LINK ADD scheduledType tinyint NOT NULL CONSTRAINT [DF_ScheduledPersonTeam_LINK_scheduledType]  DEFAULT ((1))
				WITH VALUES;

EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'1= scheduled staff role ,0- non scheduled staff' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'ScheduledPersonTeam_LINK', @level2type=N'COLUMN',@level2name=N'scheduledType'

END
GO
