USE [Allocate7]
GO

/****** Object:  Table [dbo].[DateComment]    Script Date: 02/08/2024 09:28:35 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[DateComment](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[CommentDate] [date] NOT NULL,
	[TeamId] [int] NOT NULL,
	[Comment] [nvarchar](500) NULL,
	[History] [text] NULL,
	[CreatedBy] [int] NULL,
	[CreatedDate] [datetime] NULL,
	[LastModBy] [int] NULL,
	[LastModDate] [datetime] NULL
	CONSTRAINT [PK_DateComment] PRIMARY KEY CLUSTERED 
	(
		[ID] ASC
	)
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO