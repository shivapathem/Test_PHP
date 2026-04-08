USE [Allocate7]
GO

/****** Object:  Table [dbo].[ref_LeaveApplications_Amounts]    Script Date: 03/02/2022 17:38:37 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF  NOT EXISTS (SELECT * FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[ref_LeaveApplications_Amounts]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[ref_LeaveApplications_Amounts](
	[ApplicationID] [int] NOT NULL,
	[LeaveTypeID] [int] NOT NULL,
	[Amount] [float] NOT NULL
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[ref_LeaveApplications_Amounts] ADD  CONSTRAINT [DF_ref_LeaveApplications_Amounts_Amount]  DEFAULT ((0.00)) FOR [Amount]
GO
END


