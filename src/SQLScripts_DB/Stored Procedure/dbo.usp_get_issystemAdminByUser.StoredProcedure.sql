USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_issystemAdminByUser]    Script Date: 10/11/2021 17:29:45 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
GO
DECLARE @strSQL NVARCHAR(max)
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_issystemAdminByUser]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '
--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE  [dbo].[usp_get_issystemAdminByUser]

	-- Add the parameters for the stored procedure here
	@userid INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	SELECT * FROM UserSystemRole_Link Where UserID = @userid and RoleId = 1
	
END
'

EXEC dbo.sp_executesql @strSQL

GO
