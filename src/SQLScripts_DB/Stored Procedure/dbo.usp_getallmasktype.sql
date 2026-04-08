USE [Allocate7]
GO
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_getallmasktype]') AND type in (N'P', N'PC'))
DROP PROCEDURE [dbo].[usp_getallmasktype]
GO

/****** Object:  StoredProcedure [dbo].[usp_getallmasktype]    Script Date: 30/06/2021 13:56:13 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	Get divisions by user.
-- =============================================
CREATE PROCEDURE [dbo].[usp_getallmasktype]
	-- Add the parameters for the stored procedure here
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	-- Insert statements for procedure here
	select maskTypeId,maskTypeName from masktype (NOLOCK)
END
GO

