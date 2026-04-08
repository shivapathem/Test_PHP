USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetchAdminStatusfromLeaveRequestGroups]    Script Date: 28/10/2022 13:42:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE  [dbo].[usp_fetchAdminStatusfromLeaveRequestGroups]
	@strUser varchar(50),
	@intGroupID int
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	SELECT Admin FROM Staff_Web_Config_LeaveGroups_Link (NOLOCK) WHERE (Login = @strUser AND LeaveGroupID = @intGroupID and isActive=1)
END